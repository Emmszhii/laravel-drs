<?php

namespace App\Services\Requests;

use App\Models\Borrow;
use App\Models\IcsRequest;
use App\Models\IcsRequestLine;
use App\Models\Reservation;
use App\Models\StatusChange;
use App\Models\StockConflict;
use App\Services\Stock\Reservations;
use App\Services\Stock\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ICS 213-RR request statuses and the borrowed-item lifecycle
 * (system-spec.md §3.2–3.3). Approval reserves stock; release deducts it
 * through stock entries; closing or cancelling frees what is still held.
 * Every status change is logged with the user and time.
 *
 *   draft → submitted → approved → partially_released → released → closed
 *   submitted → rejected; draft/submitted/approved (before any release) → cancelled
 */
class RequestWorkflow
{
    /** Borrowed-item transitions; returned, condemned and surrendered are final. */
    public const BORROW_TRANSITIONS = [
        'issued' => ['returned', 'for_repair', 'condemned', 'surrendered'],
        'for_repair' => ['returned', 'condemned'],
    ];

    public function __construct(private readonly StockLedger $ledger)
    {
    }

    public function submit(IcsRequest $request, ?string $userId): void
    {
        $this->move($request, ['draft'], 'submitted', $userId);
    }

    /**
     * Admin approval: per line, an approved quantity (at most the requested one) and the depot
     * it comes from. Approving more than is available is flagged for Logistics, not blocked.
     *
     * @param  array<string, array{quantity_approved: int, depot_id: string}>  $lines  keyed by line id
     * @return list<string> ids of lines whose approval exceeds available stock
     */
    public function approve(IcsRequest $request, array $lines, ?string $userId): array
    {
        return DB::transaction(function () use ($request, $lines, $userId) {
            $this->move($request, ['submitted'], 'approved', $userId);
            $flagged = [];

            foreach ($request->lines as $line) {
                $pick = $lines[$line->id] ?? null;
                if (! $pick) {
                    throw ValidationException::withMessages(['lines' => "Line {$line->id} needs an approved quantity and depot."]);
                }
                $qty = (int) $pick['quantity_approved'];
                if ($qty > $line->quantity_requested) {
                    throw ValidationException::withMessages(['lines' => 'An approved quantity cannot exceed the requested quantity.']);
                }

                $available = $this->ledger->level($line->resource_type_id, $pick['depot_id']) - Reservations::heldAt($line->resource_type_id, $pick['depot_id']);
                $line->update(['quantity_approved' => $qty, 'depot_id' => $pick['depot_id']]);
                if ($qty > 0) {
                    Reservation::create(['request_line_id' => $line->id, 'resource_type_id' => $line->resource_type_id, 'depot_id' => $pick['depot_id'], 'quantity' => $qty]);
                }

                if ($qty > $available) {
                    StockConflict::create([
                        'request_line_id' => $line->id,
                        'reason' => 'approvals_exceed_stock',
                        'detail' => "Approved {$qty} but only {$available} is available (on hand less what other approvals hold).",
                    ]);
                    $flagged[] = $line->id;
                }
            }

            return $flagged;
        });
    }

    public function reject(IcsRequest $request, string $reason, ?string $userId): void
    {
        $request->decision_reason = $reason;
        $this->move($request, ['submitted'], 'rejected', $userId, $reason);
    }

    /**
     * Logistics hands items over. Consumable lines write a release entry; a borrowable line
     * creates one Borrow per unit, each with its own issue entry.
     *
     * @param  array<string, int>  $quantities  keyed by line id: how many are handed over now
     * @param  array{borrower_name?: string, borrower_contact?: ?string, borrower_division?: string, property_numbers?: array<string, list<string>>}  $borrower
     */
    public function release(IcsRequest $request, array $quantities, array $borrower, ?string $userId): void
    {
        DB::transaction(function () use ($request, $quantities, $borrower, $userId) {
            if (! in_array($request->status, ['approved', 'partially_released'], true)) {
                throw ValidationException::withMessages(['status' => "A {$request->status} request cannot be released."]);
            }

            foreach ($request->lines()->with('resourceType', 'reservation')->get() as $line) {
                $qty = (int) ($quantities[$line->id] ?? 0);
                $left = $line->quantity_approved - $line->quantity_released;
                if ($qty <= 0) {
                    continue;
                }
                if ($qty > $left) {
                    throw ValidationException::withMessages(['quantities' => "Only {$left} of {$line->resourceType->name} is still approved for release."]);
                }

                if ($line->resourceType->is_consumable) {
                    $this->ledger->record(['resource_type_id' => $line->resource_type_id, 'depot_id' => $line->depot_id, 'quantity' => -$qty,
                        'reason' => 'release', 'request_line_id' => $line->id, 'event_tag' => $request->event_tag], $userId);
                } else {
                    if (empty($borrower['borrower_name'])) {
                        throw ValidationException::withMessages(['borrower_name' => 'Borrowed equipment needs the borrower\'s name.']);
                    }
                    $numbers = $borrower['property_numbers'][$line->id] ?? [];
                    for ($i = 0; $i < $qty; $i++) {
                        $this->issueBorrow($line, $borrower, $numbers[$i] ?? null, $request, $userId);
                    }
                }

                $line->increment('quantity_released', $qty);
                $line->reservation?->decrement('quantity', min($qty, $line->reservation->quantity));
            }

            $request->load('lines');
            $done = $request->lines->every(fn ($l) => $l->quantity_released >= (int) $l->quantity_approved);
            $this->move($request, ['approved', 'partially_released'], $done ? 'released' : 'partially_released', $userId);
            if ($done) {
                $this->close($request, $userId, 'Closed automatically: everything approved was released.');
            }
        });
    }

    /** Frees any reservation still held. Logistics closes a partly released request; a fully released one closes itself. */
    public function close(IcsRequest $request, ?string $userId, ?string $note = null): void
    {
        DB::transaction(function () use ($request, $userId, $note) {
            $this->move($request, ['partially_released', 'released'], 'closed', $userId, $note);
            $this->freeReservations($request);
        });
    }

    /** The requester may cancel before approval; the Admin, until anything is released. */
    public function cancel(IcsRequest $request, ?string $userId, bool $byAdmin): void
    {
        DB::transaction(function () use ($request, $userId, $byAdmin) {
            $this->move($request, $byAdmin ? ['draft', 'submitted', 'approved'] : ['draft', 'submitted'], 'cancelled', $userId);
            $this->freeReservations($request);
        });
    }

    public function changeBorrowStatus(Borrow $borrow, string $to, ?string $userId, ?string $note = null): void
    {
        DB::transaction(function () use ($borrow, $to, $userId, $note) {
            $allowed = self::BORROW_TRANSITIONS[$borrow->status] ?? [];
            if (! in_array($to, $allowed, true)) {
                throw ValidationException::withMessages(['status' => "A {$borrow->status} item cannot become {$to}."]);
            }

            // Only a return puts the unit back; condemned and surrendered units never come back to stock.
            if ($to === 'returned') {
                $this->ledger->record(['resource_type_id' => $borrow->resource_type_id, 'depot_id' => $borrow->depot_id, 'quantity' => 1,
                    'reason' => 'return', 'borrow_id' => $borrow->id], $userId);
            }

            StatusChange::log('borrow', $borrow->id, $borrow->status, $to, $userId, $note);
            $borrow->update(['status' => $to]);
        });
    }

    private function issueBorrow(IcsRequestLine $line, array $borrower, ?string $propertyNumber, IcsRequest $request, ?string $userId): void
    {
        $borrow = Borrow::create([
            'request_line_id' => $line->id,
            'resource_type_id' => $line->resource_type_id,
            'depot_id' => $line->depot_id,
            'property_number' => $propertyNumber,
            'borrower_division' => $borrower['borrower_division'] ?? $request->requesting_division,
            'borrower_name' => $borrower['borrower_name'],
            'borrower_contact' => $borrower['borrower_contact'] ?? null,
            'issued_at' => now(),
            'status' => 'issued',
        ]);
        $this->ledger->record(['resource_type_id' => $line->resource_type_id, 'depot_id' => $line->depot_id, 'quantity' => -1,
            'reason' => 'issue', 'request_line_id' => $line->id, 'borrow_id' => $borrow->id], $userId);
        StatusChange::log('borrow', $borrow->id, null, 'issued', $userId);
    }

    private function freeReservations(IcsRequest $request): void
    {
        Reservation::whereIn('request_line_id', $request->lines()->pluck('id'))->update(['quantity' => 0]);
    }

    private function move(IcsRequest $request, array $from, string $to, ?string $userId, ?string $note = null): void
    {
        if (! in_array($request->status, $from, true)) {
            throw ValidationException::withMessages(['status' => "A {$request->status} request cannot become {$to}."]);
        }
        StatusChange::log('ics_request', $request->id, $request->status, $to, $userId, $note);
        $request->status = $to;
        $request->save();
    }
}
