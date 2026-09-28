<?php

namespace Tests\Feature;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * DispatchController::updateStatus() deducts reserved stock once a linked
 * Allocation's dispatch reaches 'delivered' — the "resource actually left
 * the depot" event, separate from the reservation made at request approval.
 */
class DispatchDeliveryDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivering_a_dispatch_linked_to_an_allocation_deducts_stock_and_fulfills_the_request(): void
    {
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();
        $resource = Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 100,
            'quantity_reserved' => 40,
        ]);
        $request = ResourceRequest::factory()->create([
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 40,
            'status' => 'enroute',
        ]);
        $allocation = Allocation::factory()->create([
            'request_id' => $request->id,
            'depot_id' => $depot->id,
            'quantity_allocated' => 40,
            'status' => 'reserved',
        ]);
        $driver = User::factory()->create(['role' => 'ert']);
        $dispatch = Dispatch::factory()->create([
            'depot_id' => $depot->id,
            'allocation_id' => $allocation->id,
            'driver_id' => $driver->id,
            'status' => 'en_route',
        ]);

        $this->actingAs($driver)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'delivered'])
            ->assertOk();

        $this->assertDatabaseHas('app_resources', [
            'id' => $resource->id,
            'quantity_available' => 60,
            'quantity_reserved' => 0,
        ]);
        $this->assertSame('fulfilled', $allocation->fresh()->status);
        $this->assertSame('fulfilled', $request->fresh()->status);
        $this->assertSame('delivered', $dispatch->fresh()->status);
        $this->assertNotNull($dispatch->fresh()->delivered_at);
    }

    public function test_delivering_a_dispatch_with_no_linked_allocation_is_a_stock_noop(): void
    {
        $depot = Depot::factory()->create();
        $driver = User::factory()->create(['role' => 'ert']);
        $dispatch = Dispatch::factory()->create([
            'depot_id' => $depot->id,
            'allocation_id' => null,
            'driver_id' => $driver->id,
            'status' => 'en_route',
        ]);

        $this->actingAs($driver)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'delivered'])
            ->assertOk();

        $this->assertSame('delivered', $dispatch->fresh()->status);
    }

    public function test_delivering_an_already_delivered_dispatch_does_not_double_deduct(): void
    {
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();
        $resource = Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 100,
            'quantity_reserved' => 40,
        ]);
        $request = ResourceRequest::factory()->create(['resource_type_id' => $resourceType->id, 'quantity_requested' => 40]);
        $allocation = Allocation::factory()->create([
            'request_id' => $request->id,
            'depot_id' => $depot->id,
            'quantity_allocated' => 40,
            'status' => 'fulfilled',
        ]);
        $driver = User::factory()->create(['role' => 'ert']);
        $dispatch = Dispatch::factory()->create([
            'depot_id' => $depot->id,
            'allocation_id' => $allocation->id,
            'driver_id' => $driver->id,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->actingAs($driver)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'delivered'])
            ->assertOk();

        $this->assertDatabaseHas('app_resources', [
            'id' => $resource->id,
            'quantity_available' => 100,
            'quantity_reserved' => 40,
        ]);
    }
}
