<?php

namespace Tests\Feature;

use App\Events\ReplenishmentRunUpdated;
use App\Models\Depot;
use App\Models\HistoryLine;
use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
use App\Models\StockConflict;
use App\Models\StockEntry;
use App\Models\User;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Sprint 6: the evaluation tests of Chapter III that can run unattended —
 * the full budget-to-dashboard cycle, role-based access for each role, a
 * scripted offline run (30 entries on 4 devices), and dashboard figures that
 * match the database. The on-device parts (airplane mode, closing the
 * browser, Safari) are in backend/docs/evaluation-protocol.md.
 */
class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_cycle_budget_submit_to_queued_job_to_solver_to_stored_run_to_dashboard_update(): void
    {
        Event::fake([ReplenishmentRunUpdated::class]);
        Http::fake([
            '*/solve' => Http::response(['status' => 'optimal', 'rule' => 'per_item_multiplier', 'orders' => [['id' => 'x', 'x' => 3]],
                'objective' => 0.1, 'spent' => 150, 'binding' => ['budget' => true, 'space' => false], 'space_modeled' => false, 'solve_ms' => 4]),
            '*/budget-curve' => Http::response(['points' => [], 'next_step' => ['amount' => 10000, 'changes' => []]]),
        ]);
        $admin = User::factory()->create(['is_system_admin' => true]);

        $this->actingAs($admin)->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 4, 'budget' => 150])->assertStatus(202);

        $run = ReplenishmentRun::sole();
        $this->assertSame(['completed', 150.0], [$run->status, $run->result['spent'] + 0.0]);
        Event::assertDispatched(ReplenishmentRunUpdated::class, fn ($e) => $e->run->is($run) && $e->broadcastOn()[0]->name === 'replenishment');
        $this->actingAs($admin)->get("/replenishment?run={$run->id}")
            ->assertInertia(fn ($page) => $page->where('run.id', $run->id)->where('run.status', 'completed'));
    }

    /** [role, is_system_admin, endpoint, method, expected status when allowed (null = forbidden)] */
    public static function accessMatrix(): array
    {
        $roles = ['admin' => ['admin_training', true], 'logistics' => ['logistics', false], 'rp' => ['research_planning', false],
            'ert' => ['ert', false], 'ops' => ['operations', false], 'im' => ['incident_management', false], 'at' => ['admin_training', false]];
        $rules = [
            // endpoint, method, roles allowed
            ['/api/replenishment/runs', 'post', ['admin']],
            ['/api/replenishment/backtest', 'post', ['admin']],
            ['/api/replenishment/history/import', 'post', ['admin', 'logistics']],
            ['/api/stock/sync', 'post', ['admin', 'logistics']],
            ['/api/stock/verify', 'get', ['admin', 'logistics']],
            ['/api/stock/counts', 'post', ['admin', 'logistics']],
            ['/api/replenishment/report?format=csv', 'get', ['admin', 'logistics', 'rp']],
            ['/api/ics-requests', 'post', ['admin', 'logistics', 'ert', 'ops', 'im']],
            ['/api/replenishment/runs', 'get', array_keys($roles)],
            ['/api/stock/levels', 'get', array_keys($roles)],
        ];

        $cases = [];
        foreach ($rules as [$url, $method, $allowed]) {
            foreach ($roles as $key => [$role, $admin]) {
                $cases["{$method} {$url} as {$key}"] = [$role, $admin, $url, $method, in_array($key, $allowed, true)];
            }
        }

        return $cases;
    }

    #[DataProvider('accessMatrix')]
    public function test_role_based_access_for_each_role(string $role, bool $admin, string $url, string $method, bool $allowed): void
    {
        Http::fake();
        $user = User::factory()->create(['role' => $role, 'is_system_admin' => $admin]);

        $status = $this->actingAs($user)->json(strtoupper($method), $url)->baseResponse->getStatusCode();

        $allowed ? $this->assertNotSame(403, $status, "expected access, got 403") : $this->assertSame(403, $status);
    }

    /**
     * Chapter III's offline test, scripted: two laptops and two phones record 30 entries offline
     * (5 priority, 3 cases of two devices changing the same item), then each device's outbox
     * uploads — out of order and three times over. Pass: 100% saved, 0 duplicates, 0 lost,
     * priority first, every two-device conflict flagged, and the chain verifies.
     */
    public function test_scripted_offline_run_saves_everything_once_with_priority_first_and_conflicts_flagged(): void
    {
        $items = ResourceType::factory()->count(5)->create(); // one per device, plus one shared item
        $depot = Depot::factory()->create();
        $ledger = app(StockLedger::class);
        foreach ($items as $item) { // opening stock, recorded before the outage began
            $ledger->append(['device_id' => (string) Str::uuid(), 'resource_type_id' => $item->id, 'depot_id' => $depot->id, 'quantity' => 500,
                'reason' => 'receipt', 'device' => 'office', 'created_at_device' => now()->subHours(4)->toIso8601String()], null);
        }
        $logistics = User::factory()->create(['role' => 'logistics']);
        $devices = ['laptop-1', 'laptop-2', 'phone-1', 'phone-2'];
        $outboxes = array_fill_keys($devices, []);

        // 24 ordinary entries over the last three hours; each device works on its own item.
        for ($i = 0; $i < 24; $i++) {
            $outboxes[$devices[$i % 4]][] = [
                'device_id' => (string) Str::uuid(), 'resource_type_id' => $items[$i % 4]->id, 'depot_id' => $depot->id,
                'quantity' => $i % 2 ? -3 : 5, 'reason' => $i % 2 ? 'release' : 'receipt', 'device' => $devices[$i % 4],
                'created_at_device' => now()->subMinutes(180 - $i * 5)->toIso8601String(), 'priority' => $i < 5,
            ];
        }
        // 3 two-device conflicts on the shared item: laptop-2 changed it after phone-2's offline entry was made.
        foreach ([90, 60, 30] as $minutes) {
            $outboxes['phone-2'][] = ['device_id' => (string) Str::uuid(), 'resource_type_id' => $items[4]->id, 'depot_id' => $depot->id,
                'quantity' => -2, 'reason' => 'release', 'device' => 'phone-2', 'created_at_device' => now()->subMinutes($minutes)->toIso8601String()];
            $outboxes['laptop-2'][] = ['device_id' => (string) Str::uuid(), 'resource_type_id' => $items[4]->id, 'depot_id' => $depot->id,
                'quantity' => -1, 'reason' => 'release', 'device' => 'laptop-2', 'created_at_device' => now()->subMinutes($minutes - 10)->toIso8601String()];
        }
        $expected = array_sum(array_map('count', $outboxes)); // 24 ordinary + 3 two-device pairs
        $this->assertSame(30, $expected);

        // laptop-2 reconnects first, so phone-2's older offline entries arrive after the newer laptop edits.
        foreach (['laptop-2', 'laptop-1', 'phone-1', 'phone-2'] as $device) {
            foreach (range(1, 3) as $attempt) {
                $this->actingAs($logistics)->postJson('/api/stock/sync', ['entries' => array_reverse($outboxes[$device])])->assertOk();
            }
        }

        $saved = StockEntry::where('device', '!=', 'office')->count();
        $this->assertSame($expected, $saved, '100% saved, none lost');
        $this->assertSame(StockEntry::count(), StockEntry::distinct()->count('device_id'), 'zero duplicates');
        $this->assertSame(3, StockConflict::where('reason', 'concurrent_offline_edit')->count(), 'every two-device conflict flagged');
        foreach ($devices as $device) {
            $order = StockEntry::where('device', $device)->orderBy('seq')->pluck('priority')->all();
            $this->assertSame($order, collect($order)->sortDesc()->values()->all(), "{$device}: priority entries synced first");
        }
        $this->assertTrue($ledger->verify()['ok']);
    }

    public function test_dashboard_figures_match_the_database(): void
    {
        $item = ResourceType::factory()->create();
        $depot = Depot::factory()->create();
        $ledger = app(StockLedger::class);
        foreach ([40, -7, 12] as $q) {
            $ledger->record(['resource_type_id' => $item->id, 'depot_id' => $depot->id, 'quantity' => $q, 'reason' => $q > 0 ? 'receipt' : 'release'], null);
        }
        HistoryLine::create(['date' => '2026-01-10', 'resource_type_id' => $item->id, 'quantity_requested' => 9, 'purpose' => 'relief', 'requesting_division' => 'ERT']);

        $this->actingAs(User::factory()->create(['role' => 'research_planning']))->get('/stock')
            ->assertInertia(fn ($page) => $page->where('levels.0.quantity', fn ($v) => (int) $v === (int) StockEntry::sum('quantity'))
                ->where('latestFingerprint', StockEntry::orderByDesc('seq')->value('hash')));
    }
}
