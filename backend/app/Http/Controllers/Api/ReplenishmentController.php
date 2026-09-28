<?php

namespace App\Http\Controllers\Api;

use App\Events\ReplenishmentRunUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\RunReplenishmentJob;
use App\Models\Backtest;
use App\Models\DataCheckFlag;
use App\Models\QuarterBudget;
use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
use App\Models\VedPolicy;
use App\Services\Replenishment\DataChecks;
use App\Services\Replenishment\HistoryImporter;
use App\Services\Replenishment\QuarterHistory;
use App\Services\Replenishment\ReplenishmentServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quarterly replenishment (system-spec.md §2, §4). Logistics only encodes:
 * it imports the history and resolves data-check flags. The Admin (Head of
 * Office) enters the budget, runs the solver, and approves or rejects.
 */
class ReplenishmentController extends Controller
{
    public function importHistory(Request $request, HistoryImporter $importer): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);

        $result = $importer->import($request->file('file')->getRealPath(), $request->user()->id);

        return response()->json($result, $result['errors'] ? 422 : 201);
    }

    public function flags(): JsonResponse
    {
        return response()->json(
            DataCheckFlag::with('resourceType:id,name')->orderByRaw("status = 'open' DESC")->latest()->get()
        );
    }

    public function resolveFlag(Request $request, DataCheckFlag $flag): JsonResponse
    {
        $data = $request->validate(['resolution' => ['required', 'in:confirmed,corrected']]);
        abort_unless($flag->status === 'open', 422, 'This flag is already resolved.');

        $flag->update(['status' => $data['resolution'], 'resolved_by' => $request->user()->id, 'resolved_at' => now()]);

        return response()->json($flag);
    }

    public function runs(): JsonResponse
    {
        return response()->json(ReplenishmentRun::latest()->limit(50)->get()->makeHidden('inputs'));
    }

    public function show(ReplenishmentRun $run): JsonResponse
    {
        return response()->json($run);
    }

    /** Admin: enter (or update) the quarter's budget and run the solver on it. */
    public function run(Request $request, DataChecks $checks): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2100'],
            'quarter' => ['required', 'integer', 'between:1,4'],
            'budget' => ['required', 'numeric', 'min:0'],
            'rule' => ['nullable', 'in:per_item_multiplier,scenario'],
        ]);

        QuarterBudget::updateOrCreate(
            ['year' => $data['year'], 'quarter' => $data['quarter']],
            ['budget_amount' => $data['budget'], 'entered_by' => $request->user()->id],
        );

        if ($open = $checks->run($data['year'], $data['quarter'])) {
            return response()->json([
                'message' => "{$open} data-check flag(s) must be confirmed or corrected by Logistics before the solver runs.",
                'flags' => DataCheckFlag::with('resourceType:id,name')->where('status', 'open')->get(),
            ], 422);
        }

        $run = ReplenishmentRun::create([
            'year' => $data['year'],
            'quarter' => $data['quarter'],
            'budget' => $data['budget'],
            'rule' => $data['rule'] ?? 'per_item_multiplier',
            'run_by' => $request->user()->id,
        ]);
        RunReplenishmentJob::dispatch($run);

        return response()->json($run->fresh(), 202);
    }

    /**
     * Admin: rolling-origin backtest on the encoded history. Each quarter uses its
     * recorded budget where there is one, otherwise the budget given here.
     */
    public function backtest(Request $request, QuarterHistory $history, ReplenishmentServiceClient $client): JsonResponse
    {
        $data = $request->validate(['budget' => ['required', 'numeric', 'min:0']]);

        $h = $history->build(now()->year + 1, 1); // everything encoded so far
        abort_if(count($h['quarters']) < 5, 422, 'The backtest needs at least 5 encoded quarters.');

        $policies = VedPolicy::all()->keyBy('ved_class');
        $budgets = QuarterBudget::all()->keyBy(fn ($b) => "{$b->year}-{$b->quarter}");
        $payload = [
            'budget' => (float) $data['budget'],
            'budgets' => array_map(fn ($yq) => $budgets["{$yq[0]}-{$yq[1]}"]->budget_amount ?? null, $h['quarters']),
            'typhoon_quarters' => $h['event_quarters'],
            'stockout_quarters' => $h['stockout_quarters'],
            'm' => (int) $policies->max('coverage_m'),
            // ponytail: every rule starts the first backtested quarter with zero stock (the records
            // don't hold opening stock per quarter); pass initial_stock once the office has it.
            'items' => ResourceType::orderBy('name')->get()
                ->filter(fn ($t) => $t->unit_cost && ! empty($h['use'][$t->id]))
                ->map(fn ($t) => [
                    'id' => $t->id, 'q' => 0, 'unit_cost' => $t->unit_cost,
                    'weight' => $policies[$t->ved_class]->weight, 'coverage_k' => $policies[$t->ved_class]->coverage_k,
                    'history' => $h['use'][$t->id],
                ])->values()->all(),
        ];
        abort_if(! $payload['items'], 422, 'No item has both a unit cost and encoded history.');

        $backtest = Backtest::create([
            'budget' => $data['budget'],
            'quarters' => $h['quarters'],
            'inputs' => $payload,
            'result' => $client->backtest($payload),
            'run_by' => $request->user()->id,
        ]);

        return response()->json($backtest, 201);
    }

    public function latestBacktest(): JsonResponse
    {
        return response()->json(Backtest::latest()->first()?->makeHidden('inputs'));
    }

    /** Admin: the approved run becomes the quarter's purchase plan; one per quarter. */
    public function approve(Request $request, ReplenishmentRun $run): JsonResponse
    {
        abort_unless($run->status === 'completed', 422, 'Only a completed run can be approved.');

        DB::transaction(function () use ($request, $run) {
            ReplenishmentRun::where('year', $run->year)->where('quarter', $run->quarter)
                ->where('status', 'approved')->update(['status' => 'superseded']);
            $run->update([
                'status' => 'approved',
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
                'decision_comment' => $request->input('comment'),
            ]);
        });
        rescue(fn () => ReplenishmentRunUpdated::dispatch($run));

        return response()->json($run);
    }

    public function reject(Request $request, ReplenishmentRun $run): JsonResponse
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:2000']]);
        abort_unless($run->status === 'completed', 422, 'Only a completed run can be rejected.');

        $run->update([
            'status' => 'rejected',
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
            'decision_comment' => $data['comment'],
        ]);
        rescue(fn () => ReplenishmentRunUpdated::dispatch($run));

        return response()->json($run);
    }
}
