<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IcsRequestLine;
use App\Models\StockConflict;
use App\Models\StockCount;
use App\Services\Requests\RequestWorkflow;
use App\Services\Stock\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     * accepted | duplicate | deferred (its dependency hasn't arrived; retry later) |
     * rejected (invalid, with errors). Entries are checked one by one, so a bad entry
     * never holds back the rest of the batch. Releases against a request line,
     * borrowed-item issues and returns also update the request and the logbook
     * (RequestWorkflow::applyDeviceEntry).
     */
    public function sync(Request $request, StockLedger $ledger, RequestWorkflow $workflow): JsonResponse
    {
        $request->validate(['entries' => ['required', 'array', 'max:500'], 'entries.*' => ['array']]);

        $results = [];
        $valid = [];
        foreach ($request->input('entries') as $raw) {
            $v = Validator::make($raw, self::entryRules($raw));
            if ($v->fails()) {
                $results[] = self::rejected($raw['device_id'] ?? null, $v->errors()->all());
            } else {
                $valid[] = $v->validated();
            }
        }

        foreach (StockLedger::syncOrder($valid) as $entry) {
            try {
                $r = $workflow->applyDeviceEntry($entry, $request->user()->id);
                $results[] = ['device_id' => $entry['device_id'], 'status' => $r['status'], 'seq' => $r['entry']?->seq, 'conflicts' => $r['conflicts']];
            } catch (\Throwable $e) {
                report($e); // the entry's own transaction rolled back; the device keeps it and shows the error
                $results[] = self::rejected($entry['device_id'], ['The server could not record this entry.']);
            }
        }

        return response()->json(['results' => $results, 'latest_hash' => $ledger->latestFingerprint()]);
    }

    /** One outbox entry. The quantity's sign and size must fit the reason: the device doesn't get to choose. */
    private static function entryRules(array $raw): array
    {
        $sign = fn (string $attr, mixed $qty, \Closure $fail) => match ($raw['reason'] ?? null) {
            'receipt' => $qty > 0 ?: $fail('A receipt adds stock, so its quantity must be positive.'),
            'release' => $qty < 0 ?: $fail('A release removes stock, so its quantity must be negative.'),
            'issue' => (int) $qty === -1 ?: $fail('A borrowed-item issue lends out exactly one unit (quantity −1).'),
            'return' => (int) $qty === 1 ?: $fail('A borrowed-item return brings back exactly one unit (quantity 1).'),
            default => true,
        };

        return [
            'device_id' => ['required', 'uuid'],
            'resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'quantity' => ['required', 'integer', 'not_in:0', $sign],
            'reason' => ['required', Rule::in(self::ENTRY_REASONS)],
            // A device clock set ahead would flag every later entry for the item as a concurrent edit.
            'created_at_device' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toIso8601String()],
            'device' => ['nullable', 'string', 'max:100'],
            'priority' => ['boolean'],
            'depends_on_device_id' => ['nullable', 'uuid'],
            'borrow_id' => ['nullable', 'uuid', 'required_if:reason,issue,return'],
            'request_line_id' => ['nullable', 'uuid'],
            'event_tag' => ['nullable', 'string', 'max:100'],
            // A borrowed-item issue carries its logbook details (personal data: Admin and Logistics only).
            'borrower_name' => ['nullable', 'string', 'max:150', 'required_if:reason,issue'],
            'borrower_contact' => ['nullable', 'string', 'max:100'],
            'borrower_division' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'property_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    private static function rejected(mixed $deviceId, array $errors): array
    {
        return ['device_id' => is_string($deviceId) ? $deviceId : null, 'status' => 'rejected', 'seq' => null, 'conflicts' => [], 'errors' => $errors];
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
            // An "approvals exceed stock" flag has no entry, only the request line it came from.
            $flagged = $conflict->entry ?? IcsRequestLine::find($conflict->request_line_id);
            abort_unless($flagged?->depot_id, 422, 'This conflict has no item and depot to correct; resolve it with a note only.');
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
