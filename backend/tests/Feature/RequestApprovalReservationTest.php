<?php

namespace Tests\Feature;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RequestController::approve() now reserves stock at the chosen depot and
 * creates an Allocation row, in addition to its existing approval_status
 * flip — see CLAUDE.md's inventory-deduction/reservation feature.
 */
class RequestApprovalReservationTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequestAndDepot(int $available, int $reserved, int $requested): array
    {
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => $available,
            'quantity_reserved' => $reserved,
        ]);
        $zone = Zone::factory()->create();
        $request = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => $requested,
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);

        return [$depot, $resourceType, $request];
    }

    public function test_approving_with_sufficient_stock_reserves_it_and_creates_an_allocation(): void
    {
        [$depot, , $request] = $this->makeRequestAndDepot(available: 100, reserved: 0, requested: 40);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)
            ->patchJson("/api/requests/{$request->id}/approve", ['depot_id' => $depot->id])
            ->assertOk();

        $this->assertDatabaseHas('app_resources', [
            'depot_id' => $depot->id,
            'resource_type_id' => $request->resource_type_id,
            'quantity_reserved' => 40,
        ]);
        $this->assertDatabaseHas('app_allocations', [
            'request_id' => $request->id,
            'depot_id' => $depot->id,
            'quantity_allocated' => 40,
            'status' => 'reserved',
        ]);
        $this->assertSame('approved', $request->fresh()->approval_status);
    }

    public function test_approving_at_exactly_available_stock_succeeds(): void
    {
        [$depot, , $request] = $this->makeRequestAndDepot(available: 40, reserved: 0, requested: 40);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)
            ->patchJson("/api/requests/{$request->id}/approve", ['depot_id' => $depot->id])
            ->assertOk();

        $this->assertSame('approved', $request->fresh()->approval_status);
    }

    public function test_approving_with_insufficient_stock_is_rejected_and_reserves_nothing(): void
    {
        [$depot, , $request] = $this->makeRequestAndDepot(available: 100, reserved: 70, requested: 40);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)
            ->patchJson("/api/requests/{$request->id}/approve", ['depot_id' => $depot->id])
            ->assertStatus(422);

        $this->assertDatabaseHas('app_resources', [
            'depot_id' => $depot->id,
            'resource_type_id' => $request->resource_type_id,
            'quantity_reserved' => 70,
        ]);
        $this->assertDatabaseMissing('app_allocations', ['request_id' => $request->id]);
        $this->assertSame('pending', $request->fresh()->approval_status);
    }

    public function test_approving_against_a_depot_with_no_matching_resource_row_is_rejected(): void
    {
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();
        $zone = Zone::factory()->create();
        $request = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 10,
            'approval_status' => 'pending',
        ]);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)
            ->patchJson("/api/requests/{$request->id}/approve", ['depot_id' => $depot->id])
            ->assertStatus(422);
    }
}
