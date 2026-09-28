<?php

namespace App\Http\Controllers\Api;

use App\Events\ReplenishmentRunUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\RunReplenishmentJob;
use App\Models\DataCheckFlag;
use App\Models\QuarterBudget;
use App\Models\ReplenishmentRun;
use App\Services\Replenishment\DataChecks;
use App\Services\Replenishment\HistoryImporter;
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
            'run_by' => $request->user()->id,
        ]);
        RunReplenishmentJob::dispatch($run);

        return response()->json($run->fresh(), 202);
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
