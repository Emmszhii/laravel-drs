<?php

namespace Tests\Feature;

use App\Models\Borrow;
use App\Models\Depot;
use App\Models\IcsRequest;
use App\Models\Reservation;
use App\Models\ResourceType;
use App\Models\StatusChange;
use App\Models\StockConflict;
use App\Models\StockEntry;
use App\Models\User;
use App\Services\Replenishment\QuarterHistory;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Sprint 5: ICS 213-RR requests (every status), reservation on approval and
 * deduction on release, the borrowed-item lifecycle, borrower privacy, and
 * the quarterly report (Chapter III, Integration Testing Procedure).
 */
class IcsRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ResourceType $food;

    private ResourceType $radio;

    private Depot $depot;

    private User $operations;

    private User $admin;

    private User $logistics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->food = ResourceType::factory()->create(['name' => 'Family food packs', 'unit' => 'packs', 'is_consumable' => true]);
        $this->radio = ResourceType::factory()->create(['name' => 'Handheld radios', 'unit' => 'units', 'is_consumable' => false]);
        $this->depot = Depot::factory()->create();
        $ledger = app(StockLedger::class);
        $ledger->record(['resource_type_id' => $this->food->id, 'depot_id' => $this->depot->id, 'quantity' => 100, 'reason' => 'receipt'], null);
        $ledger->record(['resource_type_id' => $this->radio->id, 'depot_id' => $this->depot->id, 'quantity' => 5, 'reason' => 'receipt'], null);
        $this->operations = User::factory()->create(['role' => 'operations']);
        $this->admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true]);
        $this->logistics = User::factory()->create(['role' => 'logistics']);
    }

    private function file(array $lines, ?User $as = null): IcsRequest
    {
        $id = $this->actingAs($as ?? $this->operations)->postJson('/api/ics-requests', [
            'requesting_division' => 'ERT', 'purpose' => 'team_kit', 'event_tag' => 'Kristine', 'lines' => $lines,
        ])->assertCreated()->json('id');

        return IcsRequest::with('lines')->find($id);
    }

    private function approve(IcsRequest $r, array $qtys): void
    {
        $lines = [];
        foreach ($r->lines as $i => $line) {
            $lines[$line->id] = ['quantity_approved' => $qtys[$i], 'depot_id' => $this->depot->id, 'reason' => 'Limited stock']; // only needed when cut below the request
        }
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/approve", ['lines' => $lines])->assertOk();
    }

    private function onHand(ResourceType $t): int
    {
        return app(StockLedger::class)->level($t->id);
    }

    public function test_approve_reserves_release_deducts_and_full_release_closes(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 40]]);
        $this->assertSame('submitted', $r->status);

        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/approve", ['lines' => []])->assertForbidden();
        $this->approve($r, [30]); // approved less than requested
        $line = $r->lines[0]->fresh();
        $this->assertSame(30, Reservation::where('request_line_id', $line->id)->value('quantity'));
        $this->assertSame(100, $this->onHand($this->food)); // approval never touches stock

        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$line->id => 10]])->assertOk();
        $this->assertSame('partially_released', $r->fresh()->status);
        $this->assertSame(90, $this->onHand($this->food));
        $this->assertSame(20, Reservation::where('request_line_id', $line->id)->value('quantity'));

        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$line->id => 20]])->assertOk();
        $this->assertSame('closed', $r->fresh()->status);
        $this->assertSame(70, $this->onHand($this->food));
        $this->assertSame([40, 30, 30], [$line->fresh()->quantity_requested, $line->fresh()->quantity_approved, $line->fresh()->quantity_released]);
        $this->assertSame(0, Reservation::where('request_line_id', $line->id)->value('quantity'));
        $this->assertSame(['submitted', 'approved', 'partially_released', 'released', 'closed'],
            StatusChange::where('subject_id', $r->id)->orderBy('created_at')->pluck('to_status')->all());
        $this->assertSame(2, StockEntry::where('request_line_id', $line->id)->where('reason', 'release')->count());
    }

    public function test_releasing_more_than_approved_is_refused(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 10]]);
        $this->approve($r, [10]);

        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$r->lines[0]->id => 11]])->assertStatus(422);
        $this->assertSame(100, $this->onHand($this->food));
    }

    public function test_two_approvals_that_together_exceed_stock_raise_a_flag(): void
    {
        $a = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 70]]);
        $b = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 60]]);

        $this->approve($a, [70]);
        $this->assertSame(0, StockConflict::count());
        $this->approve($b, [60]);

        $flag = StockConflict::sole();
        $this->assertSame(['approvals_exceed_stock', $b->lines[0]->id], [$flag->reason, $flag->request_line_id]);
        $this->assertSame('approved', $b->fresh()->status); // flagged, not blocked
    }

    public function test_admin_can_approve_and_release_their_own_request(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 5]], $this->admin);
        $this->approve($r, [5]);

        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$r->lines[0]->id => 5]])->assertOk();
        $this->assertSame('closed', $r->fresh()->status);
    }

    public function test_requesters_cannot_approve_or_release(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 5]]);
        $line = [$r->lines[0]->id => ['quantity_approved' => 5, 'depot_id' => $this->depot->id]];

        $this->actingAs($this->operations)->postJson("/api/ics-requests/{$r->id}/approve", ['lines' => $line])->assertForbidden();
        $this->actingAs($this->operations)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$r->lines[0]->id => 5]])->assertForbidden();
    }

    public function test_rejection_needs_a_reason_and_only_from_submitted(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 5]]);

        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/reject")->assertStatus(422);
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/reject", ['reason' => 'Use the Q3 stock kit'])->assertOk();
        $this->assertSame('rejected', $r->fresh()->status);
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/reject", ['reason' => 'again'])->assertStatus(422);
    }

    public function test_cancel_frees_the_reservation_and_only_the_admin_cancels_after_approval(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 20]]);
        $this->approve($r, [20]);

        $this->actingAs($this->operations)->postJson("/api/ics-requests/{$r->id}/cancel")->assertStatus(422);
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/cancel")->assertOk();

        $this->assertSame('cancelled', $r->fresh()->status);
        $this->assertSame(0, (int) Reservation::sum('quantity'));
    }

    public function test_borrowed_equipment_follows_its_lifecycle_through_stock_entries(): void
    {
        $r = $this->file([['resource_type_id' => $this->radio->id, 'quantity_requested' => 2]]);
        $this->approve($r, [2]);
        $line = $r->lines[0];

        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/release", ['quantities' => [$line->id => 2]])->assertStatus(422); // borrower required
        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$r->id}/release", [
            'quantities' => [$line->id => 2], 'borrower_name' => 'J. Dela Cruz', 'borrower_contact' => '0917 000 0000',
            'property_numbers' => [$line->id => ['PR-001', 'PR-002']],
        ])->assertOk();

        $this->assertSame(3, $this->onHand($this->radio));
        [$one, $two] = Borrow::orderBy('property_number')->get()->all();
        $this->assertSame(['PR-001', 'issued'], [$one->property_number, $one->status]);

        $this->actingAs($this->logistics)->postJson("/api/borrows/{$one->id}/status", ['status' => 'returned'])->assertOk();
        $this->assertSame(4, $this->onHand($this->radio));
        $this->actingAs($this->logistics)->postJson("/api/borrows/{$one->id}/status", ['status' => 'returned'])->assertStatus(422); // returned twice

        $this->actingAs($this->logistics)->postJson("/api/borrows/{$two->id}/status", ['status' => 'for_repair'])->assertOk();
        $this->actingAs($this->logistics)->postJson("/api/borrows/{$two->id}/status", ['status' => 'condemned', 'note' => 'COA write-off'])->assertOk();
        $this->assertSame(4, $this->onHand($this->radio)); // a condemned unit never returns to stock
        $this->assertSame(['issued', 'for_repair', 'condemned'], $two->history()->pluck('to_status')->all());
    }

    public function test_releases_issues_and_returns_recorded_offline_update_the_request_and_logbook_on_sync(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 10], ['resource_type_id' => $this->radio->id, 'quantity_requested' => 1]]);
        $this->approve($r, [10, 1]);
        [$foodLine, $radioLine] = $r->lines->all();
        $this->travel(1)->hours(); // the setup receipts come before anything made offline
        $at = fn (int $min) => now()->subMinutes($min)->toIso8601String();
        $entry = fn (array $e) => $e + ['device_id' => (string) Str::uuid(), 'depot_id' => $this->depot->id, 'device' => 'phone-1'];

        $release = $entry(['resource_type_id' => $this->food->id, 'quantity' => -10, 'reason' => 'release', 'request_line_id' => $foodLine->id, 'created_at_device' => $at(30)]);
        $borrowId = (string) Str::uuid();
        $issue = $entry(['resource_type_id' => $this->radio->id, 'quantity' => -1, 'reason' => 'issue', 'request_line_id' => $radioLine->id,
            'borrow_id' => $borrowId, 'borrower_name' => 'J. Dela Cruz', 'created_at_device' => $at(20)]);
        $return = $entry(['resource_type_id' => $this->radio->id, 'quantity' => 1, 'reason' => 'return', 'borrow_id' => $borrowId,
            'depends_on_device_id' => $issue['device_id'], 'created_at_device' => $at(25), 'priority' => true]);

        // Sent return-first; uploaded twice, as a dropped connection would.
        foreach ([1, 2] as $_) {
            $this->actingAs($this->logistics)->postJson('/api/stock/sync', ['entries' => [$return, $release, $issue]])->assertOk();
        }

        $this->assertSame(['issue', 'return', 'release'], StockEntry::where('device', 'phone-1')->orderBy('seq')->pluck('reason')->all());
        $this->assertSame([10, 1], $r->lines()->orderBy('quantity_requested', 'desc')->pluck('quantity_released')->all());
        $this->assertSame('closed', $r->fresh()->status);
        $this->assertSame(['J. Dela Cruz', 'returned', 'ERT'], [Borrow::find($borrowId)->borrower_name, Borrow::find($borrowId)->status, Borrow::find($borrowId)->borrower_division]);
        $this->assertSame([90, 5], [$this->onHand($this->food), $this->onHand($this->radio)]);
        $this->assertSame(0, StockConflict::count());
    }

    public function test_an_offline_release_the_request_no_longer_allows_is_kept_and_flagged(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 10]]);
        $this->approve($r, [4]);

        $this->actingAs($this->logistics)->postJson('/api/stock/sync', ['entries' => [[
            'device_id' => (string) Str::uuid(), 'resource_type_id' => $this->food->id, 'depot_id' => $this->depot->id, 'quantity' => -6,
            'reason' => 'release', 'request_line_id' => $r->lines[0]->id, 'created_at_device' => now()->toIso8601String(),
        ]]])->assertOk()->assertJsonPath('results.0.conflicts', ['release_not_approved']);

        $this->assertSame(94, $this->onHand($this->food));
        $this->assertSame(0, $r->lines[0]->fresh()->quantity_released);
    }

    public function test_borrower_names_and_contacts_are_shown_only_to_the_admin_and_logistics(): void
    {
        Borrow::create(['resource_type_id' => $this->radio->id, 'depot_id' => $this->depot->id, 'borrower_division' => 'ERT',
            'borrower_name' => 'J. Dela Cruz', 'borrower_contact' => '0917', 'issued_at' => now(), 'status' => 'issued']);

        $this->actingAs($this->operations)->getJson('/api/borrows')->assertOk()->assertJsonMissingPath('0.borrower_name');
        $this->actingAs(User::factory()->create(['role' => 'research_planning']))->getJson('/api/borrows')->assertJsonMissingPath('0.borrower_contact');
        $this->actingAs($this->logistics)->getJson('/api/borrows')->assertJsonPath('0.borrower_name', 'J. Dela Cruz');
        $this->actingAs($this->admin)->getJson('/api/borrows')->assertJsonPath('0.borrower_contact', '0917');
    }

    public function test_filed_requests_feed_back_into_the_quarterly_history(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 40]]);
        $r->update(['date' => '2026-02-10']);

        $h = app(QuarterHistory::class)->build(2026, 2);

        $this->assertSame([40], $h['use'][$this->food->id]);
        $this->assertSame([0], $h['event_quarters']); // tagged Kristine
    }

    public function test_rejected_or_backdated_requests_do_not_become_demand(): void
    {
        $r = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 40]]);
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/reject", ['reason' => 'Not needed'])->assertOk();
        $r->update(['date' => '2026-02-10']);
        $this->assertSame([0], app(QuarterHistory::class)->build(2026, 2)['use'][$this->food->id] ?? [0]);

        $this->actingAs($this->operations)->postJson('/api/ics-requests', [
            'requesting_division' => 'ERT', 'purpose' => 'team_kit', 'date' => now()->subYear()->toDateString(),
            'lines' => [['resource_type_id' => $this->food->id, 'quantity_requested' => 99999]],
        ])->assertStatus(422)->assertJsonValidationErrors('date');
    }

    public function test_an_approval_conflict_can_be_resolved_with_a_correction(): void
    {
        $a = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 70]]);
        $b = $this->file([['resource_type_id' => $this->food->id, 'quantity_requested' => 60]]);
        $this->approve($a, [70]);
        $this->approve($b, [60]);

        $this->actingAs($this->logistics)->postJson('/api/stock/conflicts/'.StockConflict::sole()->id.'/resolve', ['note' => 'Found 30 more in the back room', 'correction' => 30])->assertOk();

        $this->assertSame(130, $this->onHand($this->food));
        $this->assertSame('resolved', StockConflict::sole()->status);
    }

    public function test_quarterly_report_exports_pdf_and_csv_with_the_fingerprint(): void
    {
        $fingerprint = app(StockLedger::class)->latestFingerprint();

        $csv = $this->actingAs($this->logistics)->get('/api/replenishment/report?format=csv&year=2026&quarter=4');
        $csv->assertOk();
        $this->assertStringContainsString($fingerprint, $csv->streamedContent());
        $this->assertStringContainsString('Family food packs', $csv->streamedContent());

        $pdf = $this->actingAs(User::factory()->create(['role' => 'research_planning']))->get('/api/replenishment/report?year=2026&quarter=4');
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));

        $this->actingAs($this->operations)->get('/api/replenishment/report')->assertForbidden();
    }
}
