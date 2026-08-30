<?php

namespace Tests\Unit\Models;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use App\Models\OptimizationRun;
use App\Models\ResourceRequest;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $disaster = Disaster::factory()->create([
            'signal' => '3',
            'lat' => '14.1',
            'lng' => '121.1',
            'external_updated_at' => '2026-01-01 00:00:00',
        ]);

        $this->assertIsInt($disaster->signal);
        $this->assertIsFloat($disaster->lat);
        $this->assertIsFloat($disaster->lng);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $disaster->external_updated_at);
    }

    public function test_zones_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $otherDisaster = Disaster::factory()->create();

        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        Zone::factory()->create(['disaster_id' => $otherDisaster->id]);

        $this->assertCount(1, $disaster->zones);
        $this->assertTrue($disaster->zones->first()->is($zone));
    }

    /**
     * app_requests has no disaster_id column — it links to a disaster only
     * indirectly through app_zones.disaster_id. This is the crux of the
     * hasManyThrough(Zone::class, ...) relation defined on Disaster::requests().
     */
    public function test_requests_relation_traverses_through_zones(): void
    {
        $disaster = Disaster::factory()->create();
        $zoneA = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $zoneB = Zone::factory()->create(['disaster_id' => $disaster->id]);

        $requestA = ResourceRequest::factory()->create(['zone_id' => $zoneA->id]);
        $requestB = ResourceRequest::factory()->create(['zone_id' => $zoneB->id]);

        $otherDisaster = Disaster::factory()->create();
        $otherZone = Zone::factory()->create(['disaster_id' => $otherDisaster->id]);
        ResourceRequest::factory()->create(['zone_id' => $otherZone->id]);

        $requests = $disaster->requests;

        $this->assertCount(2, $requests);
        $this->assertTrue($requests->pluck('id')->contains($requestA->id));
        $this->assertTrue($requests->pluck('id')->contains($requestB->id));
    }

    public function test_requests_relation_is_empty_when_disaster_has_no_zones(): void
    {
        $disaster = Disaster::factory()->create();

        $this->assertCount(0, $disaster->requests);
    }

    public function test_optimization_runs_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $otherDisaster = Disaster::factory()->create();

        $run = OptimizationRun::factory()->create(['disaster_id' => $disaster->id]);
        OptimizationRun::factory()->create(['disaster_id' => $otherDisaster->id]);

        $this->assertCount(1, $disaster->optimizationRuns);
        $this->assertTrue($disaster->optimizationRuns->first()->is($run));
    }

    public function test_evacuation_centers_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $otherDisaster = Disaster::factory()->create();

        $center = EvacuationCenter::factory()->create(['disaster_id' => $disaster->id]);
        EvacuationCenter::factory()->create(['disaster_id' => $otherDisaster->id]);

        $this->assertCount(1, $disaster->evacuationCenters);
        $this->assertTrue($disaster->evacuationCenters->first()->is($center));
    }

    public function test_ndrrmc_summary_sums_zone_level_indicators(): void
    {
        $disaster = Disaster::factory()->create();

        Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'casualties_dead' => 2,
            'casualties_injured' => 5,
            'casualties_missing' => 1,
            'families_affected' => 10,
            'houses_totally_damaged' => 3,
            'houses_partially_damaged' => 7,
            'estimated_damage_cost' => 100000.50,
        ]);

        Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'casualties_dead' => 1,
            'casualties_injured' => 2,
            'casualties_missing' => 0,
            'families_affected' => 5,
            'houses_totally_damaged' => 1,
            'houses_partially_damaged' => 2,
            'estimated_damage_cost' => 50000.25,
        ]);

        $summary = $disaster->ndrrmcSummary();

        $this->assertSame(3, $summary['casualties_dead']);
        $this->assertSame(7, $summary['casualties_injured']);
        $this->assertSame(1, $summary['casualties_missing']);
        $this->assertSame(15, $summary['families_affected']);
        $this->assertSame(4, $summary['houses_totally_damaged']);
        $this->assertSame(9, $summary['houses_partially_damaged']);
        $this->assertSame(150000.75, $summary['estimated_damage_cost']);
    }

    public function test_ndrrmc_summary_is_zeroed_when_disaster_has_no_zones(): void
    {
        $disaster = Disaster::factory()->create();

        $summary = $disaster->ndrrmcSummary();

        $this->assertSame(0, $summary['casualties_dead']);
        $this->assertSame(0.0, $summary['estimated_damage_cost']);
    }
}
