<?php

namespace Tests\Unit\Models;

use App\Models\Disaster;
use App\Models\ResourceRequest;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $zone = Zone::factory()->create([
            'population_affected' => '1000',
            'lat' => '14.1',
            'lng' => '121.1',
            'casualties_dead' => '2',
            'casualties_injured' => '5',
            'casualties_missing' => '1',
            'families_affected' => '50',
            'houses_totally_damaged' => '10',
            'houses_partially_damaged' => '20',
            'estimated_damage_cost' => '150000.50',
        ]);

        $this->assertIsInt($zone->population_affected);
        $this->assertIsFloat($zone->lat);
        $this->assertIsFloat($zone->lng);
        $this->assertIsInt($zone->casualties_dead);
        $this->assertIsInt($zone->casualties_injured);
        $this->assertIsInt($zone->casualties_missing);
        $this->assertIsInt($zone->families_affected);
        $this->assertIsInt($zone->houses_totally_damaged);
        $this->assertIsInt($zone->houses_partially_damaged);
        $this->assertIsFloat($zone->estimated_damage_cost);
    }

    public function test_disaster_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue($zone->disaster->is($disaster));
    }

    public function test_requests_relation_returns_only_this_zones_requests(): void
    {
        $zone = Zone::factory()->create();
        $otherZone = Zone::factory()->create();

        $request = ResourceRequest::factory()->create(['zone_id' => $zone->id]);
        ResourceRequest::factory()->create(['zone_id' => $otherZone->id]);

        $this->assertCount(1, $zone->requests);
        $this->assertTrue($zone->requests->first()->is($request));
    }
}
