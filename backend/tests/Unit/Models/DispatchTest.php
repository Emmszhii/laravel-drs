<?php

namespace Tests\Unit\Models;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\ResourceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_lat_lng_and_timestamp_casts(): void
    {
        $dispatch = Dispatch::factory()->create([
            'origin_lat' => '14.1',
            'origin_lng' => '121.1',
            'destination_lat' => '14.2',
            'destination_lng' => '121.2',
            'current_lat' => '14.15',
            'current_lng' => '121.15',
            'last_position_at' => '2026-01-01 00:00:00',
            'dispatched_at' => '2026-01-01 01:00:00',
            'delivered_at' => '2026-01-01 02:00:00',
        ]);

        $this->assertIsFloat($dispatch->origin_lat);
        $this->assertIsFloat($dispatch->destination_lng);
        $this->assertIsFloat($dispatch->current_lat);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $dispatch->last_position_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $dispatch->dispatched_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $dispatch->delivered_at);
    }

    public function test_disaster_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $dispatch = Dispatch::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue($dispatch->disaster->is($disaster));
    }

    public function test_zone_relation(): void
    {
        $zone = Zone::factory()->create();
        $dispatch = Dispatch::factory()->create(['zone_id' => $zone->id]);

        $this->assertTrue($dispatch->zone->is($zone));
    }

    public function test_depot_relation(): void
    {
        $depot = Depot::factory()->create();
        $dispatch = Dispatch::factory()->create(['depot_id' => $depot->id]);

        $this->assertTrue($dispatch->depot->is($depot));
    }

    public function test_allocation_relation(): void
    {
        $allocation = Allocation::factory()->create();
        $dispatch = Dispatch::factory()->create(['allocation_id' => $allocation->id]);

        $this->assertTrue($dispatch->allocation->is($allocation));
    }

    public function test_resource_type_relation(): void
    {
        $resourceType = ResourceType::factory()->create();
        $dispatch = Dispatch::factory()->create(['resource_type_id' => $resourceType->id]);

        $this->assertTrue($dispatch->resourceType->is($resourceType));
    }

    public function test_vehicle_relation(): void
    {
        $vehicle = Vehicle::factory()->create();
        $dispatch = Dispatch::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertTrue($dispatch->vehicle->is($vehicle));
    }

    public function test_driver_relation_resolves_to_a_user(): void
    {
        $driver = User::factory()->create();
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id]);

        $this->assertTrue($dispatch->driver->is($driver));
    }

    public function test_created_by_relation_resolves_to_a_user(): void
    {
        $creator = User::factory()->create();
        $dispatch = Dispatch::factory()->create(['created_by' => $creator->id]);

        $this->assertTrue($dispatch->createdBy->is($creator));
    }
}
