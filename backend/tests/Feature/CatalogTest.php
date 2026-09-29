<?php

namespace Tests\Feature;

use App\Jobs\RunReplenishmentJob;
use App\Models\Depot;
use App\Models\ResourceType;
use App\Models\StockEntry;
use App\Models\User;
use App\Models\VedPolicy;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The item catalog, depots and VED policy: the inputs the replenishment model
 * reads (system-spec.md §3.1, §4.3, §4.5).
 */
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private function keeper(): User
    {
        return User::factory()->create(['role' => 'admin_training']);
    }

    public function test_an_item_saves_its_price_ved_class_space_and_kind(): void
    {
        $id = $this->actingAs($this->keeper())->postJson('/api/resource-types', [
            'name' => 'Handheld radios', 'unit' => 'units', 'unit_cost' => 4500, 'ved_class' => 'V',
            'space_per_unit' => 0.002, 'is_consumable' => false,
        ])->assertCreated()->json('data.id');

        $item = ResourceType::find($id);
        $this->assertSame([4500.0, 'V', 0.002, false], [$item->unit_cost, $item->ved_class, $item->space_per_unit, $item->is_consumable]);

        $this->actingAs($this->keeper())->postJson('/api/resource-types', ['name' => 'Bad', 'unit' => 'x', 'ved_class' => 'X'])
            ->assertStatus(422);
    }

    public function test_an_item_with_stock_entries_cannot_be_deleted(): void
    {
        $item = ResourceType::factory()->create();
        app(StockLedger::class)->record(['resource_type_id' => $item->id, 'depot_id' => Depot::factory()->create()->id, 'quantity' => 3, 'reason' => 'receipt'], null);

        $this->actingAs($this->keeper())->deleteJson("/api/resource-types/{$item->id}")->assertStatus(422);
        $this->assertSame(1, StockEntry::count());
    }

    public function test_a_depot_saves_free_space_without_map_coordinates(): void
    {
        $id = $this->actingAs($this->keeper())->postJson('/api/depots', [
            'name' => 'CDRRMO Main Storage', 'address' => 'San Pablo City, Laguna', 'status' => 'OPERATIONAL', 'free_space' => 40.5,
        ])->assertCreated()->json('data.id');

        $this->assertSame(40.5, Depot::find($id)->free_space);
    }

    public function test_depot_space_is_sent_to_the_solver_only_when_every_depot_is_measured(): void
    {
        $this->assertNull(RunReplenishmentJob::freeSpace()); // no depots yet

        Depot::factory()->create(['free_space' => 30]);
        $unmeasured = Depot::factory()->create(['free_space' => null]);
        $this->assertNull(RunReplenishmentJob::freeSpace());

        $unmeasured->update(['free_space' => 12.5]);
        $this->assertSame(42.5, RunReplenishmentJob::freeSpace());
    }

    public function test_the_admin_confirms_the_ved_policy_with_position_and_date(): void
    {
        $admin = User::factory()->create(['role' => 'research_planning', 'is_system_admin' => true]);
        $body = ['coverage_k' => 7, 'coverage_m' => 8, 'weight' => 3, 'confirmed_by_position' => 'Logistics Officer', 'confirmed_at' => '2026-10-01'];

        $this->actingAs(User::factory()->create(['role' => 'logistics']))->patchJson('/api/replenishment/policies/V', $body)->assertForbidden();
        $this->actingAs($admin)->patchJson('/api/replenishment/policies/V', $body)->assertOk();
        $this->actingAs($admin)->patchJson('/api/replenishment/policies/V', ['coverage_k' => 9] + $body)->assertStatus(422); // k can't exceed M

        $v = VedPolicy::find('V');
        $this->assertSame([7, 'Logistics Officer', '2026-10-01'], [$v->coverage_k, $v->confirmed_by_position, $v->confirmed_at->toDateString()]);
    }
}
