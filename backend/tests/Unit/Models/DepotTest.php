<?php

namespace Tests\Unit\Models;

use App\Models\Depot;
use App\Models\Resource;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotTest extends TestCase
{
    use RefreshDatabase;

    public function test_lat_lng_cast_to_float(): void
    {
        $depot = Depot::factory()->create(['lat' => '14.5', 'lng' => '121.25']);

        $this->assertIsFloat($depot->lat);
        $this->assertIsFloat($depot->lng);
        $this->assertSame(14.5, $depot->lat);
        $this->assertSame(121.25, $depot->lng);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $depot = Depot::create([
            'name' => 'Test Depot',
            'address' => '123 Test St',
            'status' => 'OPERATIONAL',
            'lat' => 14.1,
            'lng' => 121.1,
            'contact_first_name' => 'Juan',
            'contact_last_name' => 'Dela Cruz',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '+63 912 345 6789',
        ]);

        $this->assertSame('Test Depot', $depot->name);
        $this->assertSame('OPERATIONAL', $depot->status);
        $this->assertSame('Juan', $depot->contact_first_name);
    }

    public function test_resources_relation_returns_only_this_depots_resources(): void
    {
        $depot = Depot::factory()->create();
        $otherDepot = Depot::factory()->create();

        $resource = Resource::factory()->create(['depot_id' => $depot->id]);
        Resource::factory()->create(['depot_id' => $otherDepot->id]);

        $this->assertCount(1, $depot->resources);
        $this->assertTrue($depot->resources->first()->is($resource));
    }

    public function test_vehicles_relation_returns_only_this_depots_vehicles(): void
    {
        $depot = Depot::factory()->create();
        $otherDepot = Depot::factory()->create();

        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id]);
        Vehicle::factory()->create(['depot_id' => $otherDepot->id]);

        $this->assertCount(1, $depot->vehicles);
        $this->assertTrue($depot->vehicles->first()->is($vehicle));
    }
}
