<?php

namespace App\Http\Controllers\Api;

use App\Events\ReplenishmentRunUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\RunReplenishmentJob;
use App\Models\Backtest;
use App\Models\DataCheckFlag;
use App\Models\ItemQuarter;
use App\Models\QuarterBudget;
use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
use App\Models\VedPolicy;
use App\Services\Replenishment\DataChecks;
use App\Services\Replenishment\HistoryImporter;
use App\Services\Replenishment\QuarterHistory;
use App\Services\Replenishment\QuarterRecordsImporter;
use App\Services\Replenishment\ReplenishmentServiceClient;
use App\Services\Reports\InventoryBurnRateCsvExporter;
use App\Services\Reports\InventoryBurnRateReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]); // KB: twelve quarters of request lines fit easily

        $result = $importer->import($request->file('file')->getRealPath(), $request->user()->id);

        return response()->json($result, $result['errors'] ? 422 : 201);
    }

    /** The data-request workbook's quarterly sheets: deliveries, stock at quarter end, budgets (each as CSV). */
    public function importRecords(Request $request, string $kind, QuarterRecordsImporter $importer): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $result = $importer->import($kind, $request->file('file')->getRealPath(), $request->user()->id);

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

    /** Admin: edit a VED class's coverage and weight, recording who confirmed it and when (Table 4). */
    public function updatePolicy(Request $request, VedPolicy $policy): JsonResponse
    {
        $data = $request->validate([
            'coverage_m' => ['required', 'integer', 'min:1', 'max:12'],
            'coverage_k' => ['required', 'integer', 'min:1', 'lte:coverage_m'],
            'weight' => ['required', 'numeric', 'gt:0'],
            'confirmed_by_position' => ['required', 'string', 'max:255'],
            'confirmed_at' => ['required', 'date'],
        ]);

        $policy->update($data);

        return response()->json($policy);
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
        $data = $request->validate([
            'budget' => ['required', 'numeric', 'min:0'],
            // Which event tags were typhoons (Chapter III: a typhoon quarter is tagged with a named typhoon).
            // Left out, every tag counts.
            'typhoon_tags' => ['sometimes', 'array'],
            'typhoon_tags.*' => ['string'],
        ]);

        $h = $history->build(now()->year + 1, 1); // everything encoded so far
        abort_if(count($h['quarters']) < 5, 422, 'The backtest needs at least 5 encoded quarters.');

        $policies = VedPolicy::all()->keyBy('ved_class');
        $budgets = QuarterBudget::all()->keyBy(fn ($b) => "{$b->year}-{$b->quarter}");
        $index = array_flip(array_map(fn ($yq) => "{$yq[0]}-{$yq[1]}", $h['quarters']));
        $records = ItemQuarter::all();
        $typhoonTags = $data['typhoon_tags'] ?? array_values(array_unique(array_merge([], ...array_values($h['event_tags']))));
        $start = 4; // the 5th encoded quarter

        $items = ResourceType::orderBy('name')->get()
            ->filter(fn ($t) => $t->unit_cost && ! empty($h['use'][$t->id]))
            ->map(fn ($t) => [
                'id' => $t->id, 'q' => 0, 'unit_cost' => $t->unit_cost,
                'weight' => $policies[$t->ved_class]->weight, 'coverage_k' => $policies[$t->ved_class]->coverage_k,
                'history' => $h['use'][$t->id],
            ])->values()->all();
        $ids = array_flip(array_column($items, 'id'));

        // The office's actual orders, by quarter index, from the delivery records it encoded.
        $actual = [];
        foreach ($records->whereNotNull('quantity_ordered') as $r) {
            if (isset($index["{$r->year}-{$r->quarter}"], $ids[$r->resource_type_id])) {
                $actual[(string) $index["{$r->year}-{$r->quarter}"]][$r->resource_type_id] = $r->quantity_ordered;
            }
        }
        // Opening stock: what was on hand when the quarter before the first backtested one closed.
        [$y, $q] = $h['quarters'][$start - 1];
        $opening = $records->where('year', $y)->where('quarter', $q)->whereNotNull('quantity_available_at_close')
            ->filter(fn ($r) => isset($ids[$r->resource_type_id]))
            ->mapWithKeys(fn ($r) => [$r->resource_type_id => $r->quantity_available_at_close])->all();

        $payload = [
            'budget' => (float) $data['budget'],
            'budgets' => array_map(fn ($yq) => $budgets["{$yq[0]}-{$yq[1]}"]->budget_amount ?? null, $h['quarters']),
            'start' => $start,
            'typhoon_quarters' => array_keys(array_filter($h['event_tags'], fn ($tags) => array_intersect($tags, $typhoonTags))),
            'stockout_quarters' => $h['stockout_quarters'],
            'm' => (int) $policies->max('coverage_m'),
            // Items with no closing count start at zero; the page says how many had one.
            'initial_stock' => (object) $opening,
            'actual_orders' => (object) $actual,
            'items' => $items,
        ];
        abort_if(! $payload['items'], 422, 'No item has both a unit cost and encoded history.');

        $backtest = Backtest::create([
            'budget' => $data['budget'],
            'quarters' => $h['quarters'],
            'inputs' => $payload + ['typhoon_tags' => $typhoonTags],
            'result' => $client->backtest($payload),
            'run_by' => $request->user()->id,
        ]);

        return response()->json($backtest, 201);
    }

    /**
     * Burn-rate and reorder report, PDF or CSV, with the latest fingerprint printed.
     * One quarter, or a range within one year (quarter..quarter_to, e.g. Q1–Q4).
     */
    public function report(Request $request, InventoryBurnRateReportBuilder $builder): SymfonyResponse
    {
        $data = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2100'],
            'quarter' => ['nullable', 'integer', 'between:1,4'],
            'quarter_to' => ['nullable', 'integer', 'between:1,4', 'gte:quarter'],
            'format' => ['nullable', 'in:pdf,csv'],
        ]);
        $reports = [$builder->build($data['year'] ?? null, $data['quarter'] ?? null)];
        for ($q = $reports[0]['quarter'] + 1; $q <= ($data['quarter_to'] ?? 0); $q++) {
            $reports[] = $builder->build($reports[0]['year'], $q);
        }
        $name = "burn-rate-reorder-{$reports[0]['year']}-q{$reports[0]['quarter']}".(count($reports) > 1 ? '-q'.end($reports)['quarter'] : '');

        if (($data['format'] ?? 'pdf') === 'csv') {
            return response()->streamDownload(function () use ($reports) {
                foreach ($reports as $i => $report) {
                    $i && print "\n";
                    app(InventoryBurnRateCsvExporter::class)->stream($report);
                }
            }, "{$name}.csv", ['Content-Type' => 'text/csv']);
        }

        return Pdf::loadView('reports.inventory-burn-rate', ['reports' => $reports])->download("{$name}.pdf");
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
