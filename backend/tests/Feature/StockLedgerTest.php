<?php

namespace Tests\Feature;

use App\Models\DataCheckFlag;
use App\Models\Depot;
use App\Models\ResourceType;
use App\Models\StockConflict;
use App\Models\StockCount;
use App\Models\StockEntry;
use App\Models\User;
use App\Services\Replenishment\DataChecks;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

/**
 * Sprint 3: offline-safe, tamper-evident stock records — the integrity
 * tests of Chapter III (Offline Resilience and Integrity Testing Procedure).
 */
class StockLedgerTest extends TestCase
{
    use RefreshDatabase;

    private ResourceType $item;

    private Depot $depot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->item = ResourceType::factory()->create();
        $this->depot = Depot::factory()->create();
    }

    private function entry(int $qty, array $extra = []): array
    {
        return $extra + [
            'device_id' => (string) Str::uuid(),
            'resource_type_id' => $this->item->id,
            'depot_id' => $this->depot->id,
            'quantity' => $qty,
            'reason' => $qty > 0 ? 'receipt' : 'release',
            'created_at_device' => now()->toIso8601String(),
            'device' => 'laptop-1',
        ];
    }

    private function logistics(): User
    {
        return User::factory()->create(['role' => 'logistics']);
    }

    private function sync(array $entries, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->logistics())->postJson('/api/stock/sync', ['entries' => $entries]);
    }

    public function test_the_same_batch_uploaded_three_times_holds_zero_duplicates(): void
    {
        $batch = [$this->entry(50), $this->entry(-10), $this->entry(5)];

        $this->sync($batch)->assertOk();
        $second = $this->sync($batch)->assertOk()->json('results');
        $this->sync($batch)->assertOk();

        $this->assertSame(3, StockEntry::count());
        $this->assertSame(['duplicate', 'duplicate', 'duplicate'], array_column($second, 'status'));
        $this->assertSame(45, app(StockLedger::class)->level($this->item->id));
    }

    public function test_priority_entries_sync_first_then_by_creation_time(): void
    {
        $old = $this->entry(10, ['created_at_device' => now()->subHours(3)->toIso8601String()]);
        $newer = $this->entry(20, ['created_at_device' => now()->subHours(2)->toIso8601String()]);
        $urgent = $this->entry(30, ['created_at_device' => now()->subHour()->toIso8601String(), 'priority' => true]);

        $this->sync([$newer, $old, $urgent])->assertOk();

        $this->assertSame([30, 10, 20], StockEntry::orderBy('seq')->pluck('quantity')->all());
    }

    public function test_an_offline_return_syncs_after_its_offline_borrow_even_when_sent_first(): void
    {
        $borrowId = (string) Str::uuid();
        $issue = $this->entry(-1, ['reason' => 'issue', 'borrow_id' => $borrowId, 'created_at_device' => now()->subMinutes(20)->toIso8601String()]);
        $return = $this->entry(1, ['reason' => 'return', 'borrow_id' => $borrowId, 'depends_on_device_id' => $issue['device_id'],
            'created_at_device' => now()->subMinutes(30)->toIso8601String(), 'priority' => true]);
        $this->sync([$this->entry(5)]);

        $this->sync([$return, $issue])->assertOk();

        $this->assertSame(['receipt', 'issue', 'return'], StockEntry::orderBy('seq')->pluck('reason')->all());
        $this->assertSame(0, StockConflict::count());
    }

    public function test_a_return_whose_borrow_has_not_arrived_is_deferred_not_lost(): void
    {
        $return = $this->entry(1, ['reason' => 'return', 'depends_on_device_id' => (string) Str::uuid()]);

        $this->sync([$return])->assertOk()->assertJsonPath('results.0.status', 'deferred');
        $this->assertSame(0, StockEntry::count());
    }

    public function test_returning_an_item_twice_is_kept_and_flagged(): void
    {
        $borrowId = (string) Str::uuid();
        $this->sync([$this->entry(1), $this->entry(-1, ['reason' => 'issue', 'borrow_id' => $borrowId])]);
        $this->sync([$this->entry(1, ['reason' => 'return', 'borrow_id' => $borrowId])]);
        $this->sync([$this->entry(1, ['reason' => 'return', 'borrow_id' => $borrowId])])
            ->assertJsonPath('results.0.conflicts', ['return_not_issued']);

        $this->assertSame(4, StockEntry::count());
    }

    public function test_an_entry_that_makes_stock_negative_is_kept_and_flagged(): void
    {
        $this->sync([$this->entry(5)]);
        $this->sync([$this->entry(-8)])->assertJsonPath('results.0.status', 'accepted')->assertJsonPath('results.0.conflicts', ['negative_stock']);

        $this->assertSame(-3, app(StockLedger::class)->level($this->item->id));
        $this->assertSame('negative_stock', StockConflict::sole()->reason);
    }

    public function test_two_devices_changing_the_same_item_offline_raise_a_flag(): void
    {
        $this->sync([$this->entry(100)]);
        // Phone was offline from an hour ago; the laptop changed the item half an hour ago.
        $this->sync([$this->entry(-5, ['device' => 'laptop-1', 'created_at_device' => now()->subMinutes(30)->toIso8601String()])]);
        $this->sync([$this->entry(-7, ['device' => 'phone-2', 'created_at_device' => now()->subHour()->toIso8601String()])])
            ->assertJsonPath('results.0.conflicts', ['concurrent_offline_edit']);
    }

    public function test_resolving_a_conflict_appends_a_correction_instead_of_editing(): void
    {
        $this->sync([$this->entry(-4)]);
        $conflict = StockConflict::sole();

        $this->actingAs($this->logistics())->postJson("/api/stock/conflicts/{$conflict->id}/resolve", ['note' => 'Receipt of 4 was never logged', 'correction' => 4])
            ->assertOk();

        $this->assertSame('resolved', $conflict->fresh()->status);
        $this->assertSame(['release', 'conflict_resolution'], StockEntry::orderBy('seq')->pluck('reason')->all());
        $this->assertSame(0, app(StockLedger::class)->level($this->item->id));
    }

    public function test_check_records_fails_at_a_row_edited_directly_in_the_database(): void
    {
        $this->sync([$this->entry(10), $this->entry(20), $this->entry(30)]);
        $this->artisan('stock:verify')->assertExitCode(0);
        $this->assertTrue(app(StockLedger::class)->verify()['ok']);

        DB::table('app_stock_entries')->where('seq', 2)->update(['quantity' => 2000]);

        $r = app(StockLedger::class)->verify();
        $this->assertFalse($r['ok']);
        $this->assertSame(2, $r['first_bad_seq']);
        $this->artisan('stock:verify')->assertExitCode(1);
    }

    public function test_a_deleted_row_is_reported_as_a_missing_entry(): void
    {
        $this->sync([$this->entry(10), $this->entry(20), $this->entry(30)]);
        DB::table('app_stock_entries')->where('seq', 2)->delete();

        $this->assertSame(['ok' => false, 'first_bad_seq' => 3], array_intersect_key(app(StockLedger::class)->verify(), ['ok' => 1, 'first_bad_seq' => 1]));
    }

    public function test_entries_cannot_be_edited_or_deleted_through_the_app(): void
    {
        $this->sync([$this->entry(10)]);
        $entry = StockEntry::sole();

        $this->expectException(LogicException::class);
        $entry->update(['quantity' => 99]);
    }

    public function test_only_stock_keepers_write_entries(): void
    {
        foreach (['ert', 'operations', 'research_planning', 'incident_management'] as $role) {
            $this->sync([$this->entry(1)], User::factory()->create(['role' => $role]))->assertForbidden();
        }
        $this->sync([$this->entry(1)], User::factory()->create(['is_system_admin' => true]))->assertOk();
    }

    public function test_data_checks_flag_negative_stock_and_count_mismatches(): void
    {
        $ledger = app(StockLedger::class);
        $ledger->record(['resource_type_id' => $this->item->id, 'depot_id' => $this->depot->id, 'quantity' => 40, 'reason' => 'receipt'], null);
        StockCount::create(['resource_type_id' => $this->item->id, 'depot_id' => $this->depot->id, 'counted_quantity' => 37, 'counted_at' => now()]);

        $other = ResourceType::factory()->create();
        $ledger->record(['resource_type_id' => $other->id, 'depot_id' => $this->depot->id, 'quantity' => -2, 'reason' => 'release'], null);

        app(DataChecks::class)->run(now()->year, now()->quarter);

        $this->assertEqualsCanonicalizing(['count_mismatch', 'negative_stock'], DataCheckFlag::pluck('rule')->all());
    }
}
