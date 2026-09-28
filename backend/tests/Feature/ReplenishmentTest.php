<?php

namespace Tests\Feature;

use App\Models\DataCheckFlag;
use App\Models\Depot;
use App\Models\HistoryLine;
use App\Models\ReplenishmentRun;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use App\Services\Replenishment\QuarterHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Sprint 1 of the new scope: Logistics only encodes (history import, flag
 * resolution); the Admin runs the solver and approves or rejects.
 */
class ReplenishmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true]);
    }

    private function logistics(): User
    {
        return User::factory()->create(['role' => 'logistics']);
    }

    private function food(): ResourceType
    {
        return ResourceType::factory()->create(['name' => 'Family food packs', 'unit' => 'packs', 'unit_cost' => 500, 'ved_class' => 'E']);
    }

    private function line(ResourceType $type, string $date, int $qty, ?string $tag = null): void
    {
        HistoryLine::create([
            'date' => $date, 'resource_type_id' => $type->id, 'quantity_requested' => $qty,
            'quantity_released' => $qty, 'purpose' => 'relief', 'event_tag' => $tag, 'requesting_division' => 'ERT',
        ]);
    }

    private function fakeSolver(): void
    {
        Http::fake([
            '*/api/v1/replenishment/budget-curve' => Http::response(['points' => [['pct' => 100, 'budget' => 1, 'expected_shortage' => 0, 'status' => 'optimal']], 'next_step' => ['amount' => 10000, 'changes' => []]]),
            '*/api/v1/replenishment/solve' => Http::response([
            'status' => 'optimal', 'rule' => 'per_item_multiplier', 'orders' => [], 'objective' => 0,
            'spent' => 0, 'binding' => ['budget' => false, 'space' => false], 'space_modeled' => false, 'solve_ms' => 3,
        ])]);
    }

    public function test_quarter_history_fills_empty_quarters_with_zero_oldest_first(): void
    {
        $food = $this->food();
        $this->line($food, '2025-02-01', 10);
        $this->line($food, '2025-03-15', 5);
        $this->line($food, '2025-09-01', 7);
        $this->line($food, '2026-01-10', 99); // in the plan quarter itself: excluded

        $h = app(QuarterHistory::class)->build(2026, 1);

        // 2025 Q2 had no line for this item (zero use); 2025 Q4 had no record for any item (not encoded).
        $this->assertSame([[2025, 1], [2025, 2], [2025, 3]], $h['quarters']);
        $this->assertSame([15, 0, 7], $h['use'][$food->id]);
    }

    public function test_only_the_admin_can_run_the_solver_and_logistics_only_encodes(): void
    {
        $this->fakeSolver();
        $body = ['year' => 2026, 'quarter' => 3, 'budget' => 200000];

        $this->actingAs($this->logistics())->postJson('/api/replenishment/runs', $body)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'research_planning']))->postJson('/api/replenishment/runs', $body)->assertForbidden();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', $body)->assertStatus(202);
        $this->actingAs($this->admin())->postJson('/api/replenishment/history/import', [])->assertForbidden();
    }

    public function test_run_sends_history_stock_less_reserved_and_ved_weight_then_completes(): void
    {
        $this->fakeSolver();
        $food = $this->food();
        $depot = Depot::factory()->create();
        // On hand comes from the stock entries, not the old aggregate row.
        app(\App\Services\Stock\StockLedger::class)->record(['resource_type_id' => $food->id, 'depot_id' => $depot->id, 'quantity' => 150, 'reason' => 'receipt'], null);
        Resource::factory()->create(['depot_id' => $depot->id, 'resource_type_id' => $food->id,
            'quantity_available' => 999, 'quantity_reserved' => 20]);
        foreach (['2025-01-10', '2025-04-10', '2025-07-10', '2025-10-10'] as $i => $date) {
            $this->line($food, $date, 100 + $i);
        }

        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 200000])
            ->assertStatus(202);

        $run = ReplenishmentRun::sole();
        $this->assertSame('completed', $run->status);
        $this->assertSame(200000.0, $run->budget);
        Http::assertSent(function (HttpRequest $req) use ($food) {
            $item = collect($req['items'])->firstWhere('id', $food->id);

            return $item['q'] === 150 && $item['reserved'] === 20 && $item['weight'] == 2 && $item['coverage_k'] === 7
                && $item['history'] === [100, 101, 102, 103] && $item['unit_cost'] == 500;
        });
    }

    public function test_run_stores_the_budget_curve_and_next_step(): void
    {
        $this->fakeSolver();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 1])->assertStatus(202);

        $result = ReplenishmentRun::sole()->result;
        $this->assertSame(100, $result['budget_curve'][0]['pct']);
        $this->assertSame(10000, $result['next_step']['amount']);
    }

    public function test_admin_can_choose_the_scenario_model(): void
    {
        $this->fakeSolver();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 1, 'rule' => 'scenario'])->assertStatus(202);

        $this->assertSame('scenario', ReplenishmentRun::sole()->rule);
        Http::assertSent(fn (HttpRequest $req) => str_ends_with($req->url(), '/solve') && $req['rule'] === 'scenario');
    }

    public function test_backtest_sends_history_budgets_and_event_quarters_and_stores_the_result(): void
    {
        Http::fake(['*/api/v1/replenishment/backtest' => Http::response(['quarters' => [], 'summary' => ['backtested_quarters' => 1]])]);
        $food = $this->food();
        foreach (['2025-01-10', '2025-04-10', '2025-07-10', '2025-10-10', '2026-01-10'] as $i => $date) {
            $this->line($food, $date, 100 + $i, $i === 2 ? 'Kristine' : null);
        }
        \App\Models\QuarterBudget::create(['year' => 2025, 'quarter' => 4, 'budget_amount' => 5000]);

        $this->actingAs($this->logistics())->postJson('/api/replenishment/backtest', ['budget' => 1000])->assertForbidden();
        $this->actingAs($this->admin())->postJson('/api/replenishment/backtest', ['budget' => 1000])->assertCreated();

        Http::assertSent(fn (HttpRequest $req) => $req['typhoon_quarters'] === [2]
            && $req['budgets'] == [null, null, null, 5000, null]
            && $req['items'][0]['history'] === [100, 101, 102, 103, 104]);
        $this->assertSame(1, \App\Models\Backtest::sole()->result['summary']['backtested_quarters']);
    }

    public function test_backtest_needs_five_encoded_quarters(): void
    {
        $food = $this->food();
        $this->line($food, '2025-01-10', 10);

        $this->actingAs($this->admin())->postJson('/api/replenishment/backtest', ['budget' => 1000])->assertStatus(422);
    }

    public function test_a_solver_outage_fails_the_run_instead_of_hanging(): void
    {
        Http::fake(['*' => Http::response('down', 503)]);
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 1])->assertStatus(202);

        $this->assertSame('failed', ReplenishmentRun::sole()->status);
    }

    public function test_dashboard_page_shows_each_role_only_its_own_actions(): void
    {
        $this->actingAs($this->admin())->get('/replenishment')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Replenishment/Index')->where('can', ['run' => true, 'encode' => false]));
        $this->actingAs($this->logistics())->get('/replenishment')->assertOk()
            ->assertInertia(fn ($page) => $page->where('can', ['run' => false, 'encode' => true]));
        $this->actingAs(User::factory()->create(['role' => 'research_planning']))->get('/replenishment')->assertOk()
            ->assertInertia(fn ($page) => $page->where('can', ['run' => false, 'encode' => false]));
    }

    public function test_an_unexplained_spike_blocks_the_run_until_logistics_resolves_it(): void
    {
        $this->fakeSolver();
        $food = $this->food();
        foreach (['2025-01-10' => 100, '2025-04-10' => 100, '2025-07-10' => 100, '2025-10-10' => 900] as $date => $qty) {
            $this->line($food, $date, $qty);
        }
        $body = ['year' => 2026, 'quarter' => 1, 'budget' => 1000];

        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', $body)->assertStatus(422)->assertJsonCount(1, 'flags');
        $flag = DataCheckFlag::sole();
        $this->assertSame([2025, 4], [$flag->year, $flag->quarter]);

        $this->actingAs($this->logistics())->postJson("/api/replenishment/flags/{$flag->id}/resolve", ['resolution' => 'confirmed'])->assertOk();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', $body)->assertStatus(202);
        $this->assertSame(1, DataCheckFlag::count()); // a confirmed flag is not raised again
    }

    public function test_idle_quarters_do_not_make_normal_use_look_like_a_spike(): void
    {
        $paper = ResourceType::factory()->create(['name' => 'Bond paper', 'unit' => 'boxes', 'ved_class' => 'D']);
        $this->line($paper, '2024-01-15', 10);
        $this->line($paper, '2024-04-15', 12); // then six idle quarters before 2026 Q1

        $this->fakeSolver();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 1])->assertStatus(202);
        $this->assertSame(0, DataCheckFlag::count());
    }

    public function test_a_tagged_spike_is_not_flagged(): void
    {
        $food = $this->food();
        foreach (['2025-01-10' => 100, '2025-04-10' => 100, '2025-07-10' => 100] as $date => $qty) {
            $this->line($food, $date, $qty);
        }
        $this->line($food, '2024-10-24', 900, 'Kristine');

        $this->fakeSolver();
        $this->actingAs($this->admin())->postJson('/api/replenishment/runs', ['year' => 2026, 'quarter' => 1, 'budget' => 1])->assertStatus(202);
    }

    public function test_admin_approves_one_run_per_quarter_and_rejection_needs_a_comment(): void
    {
        $old = ReplenishmentRun::create(['year' => 2026, 'quarter' => 3, 'budget' => 1, 'status' => 'approved']);
        $new = ReplenishmentRun::create(['year' => 2026, 'quarter' => 3, 'budget' => 2, 'status' => 'completed']);
        $other = ReplenishmentRun::create(['year' => 2026, 'quarter' => 3, 'budget' => 3, 'status' => 'completed']);

        $this->actingAs($this->logistics())->postJson("/api/replenishment/runs/{$new->id}/approve")->assertForbidden();
        $this->actingAs($this->admin())->postJson("/api/replenishment/runs/{$new->id}/approve")->assertOk();
        $this->assertSame(['superseded', 'approved'], [$old->fresh()->status, $new->fresh()->status]);

        $this->actingAs($this->admin())->postJson("/api/replenishment/runs/{$other->id}/reject")->assertStatus(422);
        $this->actingAs($this->admin())->postJson("/api/replenishment/runs/{$other->id}/reject", ['comment' => 'Budget will change'])->assertOk();
        $this->actingAs($this->admin())->postJson("/api/replenishment/runs/{$other->id}/approve")->assertStatus(422);
    }

    public function test_logistics_imports_the_encoding_template_all_or_nothing(): void
    {
        $this->food();
        $good = "date,item,standard_unit,quantity_requested,quantity_released,purpose,event_tag,requesting_division\n"
            . "2024-10-24,Family food packs,packs,120,100,relief,Kristine,ERT\n"
            . "2024-11-05,family food packs,PACKS,40,,team kit,,Operations\n";
        $bad = $good . "2024-12-01,Family food packs,boxes,-3,,picnic,,\n";

        $this->actingAs($this->logistics())
            ->post('/api/replenishment/history/import', ['file' => UploadedFile::fake()->createWithContent('bad.csv', $bad)], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonPath('imported', 0);
        $this->assertSame(0, HistoryLine::count());

        $this->actingAs($this->logistics())
            ->post('/api/replenishment/history/import', ['file' => UploadedFile::fake()->createWithContent('good.csv', $good)], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('imported', 2);
        $this->assertSame(['team_kit', null], [HistoryLine::where('quantity_requested', 40)->value('purpose'), HistoryLine::where('quantity_requested', 40)->value('quantity_released')]);
    }

    public function test_the_shipped_template_imports_cleanly(): void
    {
        foreach ([['Family food packs', 'packs'], ['Hygiene kits', 'kits'], ['Medical kits', 'kits']] as [$name, $unit]) {
            ResourceType::factory()->create(['name' => $name, 'unit' => $unit]);
        }

        $this->artisan('history:import', ['file' => base_path('docs/templates/history-encoding-template.csv')])
            ->expectsOutput('Imported 3 history line(s).')
            ->assertExitCode(0);
    }
}
