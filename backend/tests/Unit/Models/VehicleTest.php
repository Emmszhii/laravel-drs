<?php

namespace Tests\Unit\Models;

use App\Models\Depot;
use App\Models\Dispatch;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_capacity_casts_to_int(): void
    {
        $vehicle = Vehicle::factory()->create(['capacity' => '250']);

        $this->assertIsInt($vehicle->capacity);
        $this->assertSame(250, $vehicle->capacity);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $depot = Depot::factory()->create();

        $vehicle = Vehicle::create([
            'label' => 'Truck 5',
            'capacity' => 300,
            'depot_id' => $depot->id,
            'status' => 'available',
        ]);

        $this->assertSame('Truck 5', $vehicle->label);
        $this->assertSame('available', $vehicle->status);
    }

    public function test_depot_relation(): void
    {
        $depot = Depot::factory()->create();
        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id]);

        $this->assertTrue($vehicle->depot->is($depot));
    }

    public function test_dispatches_relation_returns_only_this_vehicles_dispatches(): void
    {
        $vehicle = Vehicle::factory()->create();
        $otherVehicle = Vehicle::factory()->create();

        $dispatch = Dispatch::factory()->create(['vehicle_id' => $vehicle->id]);
        Dispatch::factory()->create(['vehicle_id' => $otherVehicle->id]);

        $this->assertCount(1, $vehicle->dispatches);
        $this->assertTrue($vehicle->dispatches->first()->is($dispatch));
    }
}
