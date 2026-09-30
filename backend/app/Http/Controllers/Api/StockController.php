<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockConflict;
use App\Models\StockCount;
use App\Services\Requests\RequestWorkflow;
use App\Services\Stock\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Stock records (system-spec.md §5). Only the stock keepers (Logistics and
 * the Admin) create entries directly; everyone else changes stock through
 * requests and borrowing.
 */
class StockController extends Controller
{
    public const ENTRY_REASONS = ['receipt', 'release', 'return', 'adjustment', 'count_correction', 'issue'];

    /**
     * Idempotent batch upload from a device's offline outbox. Every entry answers
     * accepted | duplicate | deferred (its dependency hasn't arrived; retry later).
     * Releases against a request line, borrowed-item issues and returns also update
     * the request and the logbook (RequestWorkflow::applyDeviceEntry).
     */
    public function sync(Request $request, StockLedger $ledger, RequestWorkflow $workflow): JsonResponse
    {
        $data = $request->validate([
            'entries' => ['required', 'array', 'max:500'],
            'entries.*.device_id' => ['required', 'uuid'],
            'entries.*.resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'entries.*.depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'entries.*.quantity' => ['required', 'integer', 'not_in:0'],
            'entries.*.reason' => ['required', Rule::in(self::ENTRY_REASONS)],
            'entries.*.created_at_device' => ['required', 'date'],
            'entries.*.device' => ['nullable', 'string', 'max:100'],
            'entries.*.priority' => ['boolean'],
            'entries.*.depends_on_device_id' => ['nullable', 'uuid'],
            'entries.*.borrow_id' => ['nullable', 'uuid', 'required_if:entries.*.reason,issue,return'],
            'entries.*.request_line_id' => ['nullable', 'uuid'],
            'entries.*.event_tag' => ['nullable', 'string', 'max:100'],
            // A borrowed-item issue carries its logbook details (personal data: Admin and Logistics only).
            'entries.*.borrower_name' => ['nullable', 'string', 'max:150', 'required_if:entries.*.reason,issue'],
            'entries.*.borrower_contact' => ['nullable', 'string', 'max:100'],
            'entries.*.borrower_division' => ['nullable', 'string', 'max:100'],
            'entries.*.property_number' => ['nullable', 'string', 'max:100'],
        ]);

        $results = [];
        foreach (StockLedger::syncOrder($data['entries']) as $entry) {
            $r = $workflow->applyDeviceEntry($entry, $request->user()->id);
            $results[] = ['device_id' => $entry['device_id'], 'status' => $r['status'], 'seq' => $r['entry']?->seq, 'conflicts' => $r['conflicts']];
        }

        return response()->json(['results' => $results, 'latest_hash' => $ledger->latestFingerprint()]);
    }

    public function levels(StockLedger $ledger): JsonResponse
    {
        return response()->json($ledger->levels());
    }

    public function conflicts(): JsonResponse
    {
        return response()->json(StockConflict::with('entry.resourceType:id,name', 'entry.depot:id,name')->where('status', 'open')->latest()->get());
    }

    /** Resolving never edits the flagged entry: an optional correction is appended as a new entry. */
    public function resolveConflict(Request $request, StockConflict $conflict, StockLedger $ledger): JsonResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'correction' => ['nullable', 'integer', 'not_in:0'],
        ]);
        abort_unless($conflict->status === 'open', 422, 'This conflict is already resolved.');

        $correction = null;
        if (isset($data['correction'])) {
            $flagged = $conflict->entry;
            $correction = $ledger->record([
                'resource_type_id' => $flagged->resource_type_id,
                'depot_id' => $flagged->depot_id,
                'quantity' => $data['correction'],
                'reason' => 'conflict_resolution',
            ], $request->user()->id);
        }

        $conflict->update([
            'status' => 'resolved',
            'resolution_note' => $data['note'],
            'resolution_entry_id' => $correction?->id,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return response()->json($conflict);
    }

    public function storeCount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'counted_quantity' => ['required', 'integer', 'min:0'],
            'counted_at' => ['nullable', 'date'],
        ]);

        $count = StockCount::create($data + ['counted_at' => now(), 'counted_by' => $request->user()->id]);

        return response()->json($count, 201);
    }

    /** "Check records": recompute the chain and report the first entry that no longer matches. */
    public function verify(StockLedger $ledger): JsonResponse
    {
        return response()->json($ledger->verify());
    }
}
