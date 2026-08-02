<?php

namespace Tests\Unit;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\Zone;
use App\Services\DisasterFeed\DepotCapacityChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotCapacityCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_true_when_there_is_no_outstanding_demand(): void
    {
        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue(app(DepotCapacityChecker::class)->canCoverOutstanding($disaster));
    }

    public function test_true_when_operational_depot_stock_covers_the_open_request(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();

        ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 100,
            'status' => 'pending',
        ]);

        $depot = Depot::factory()->create(['status' => 'OPERATIONAL']);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 200,
            'quantity_reserved' => 0,
        ]);

        $this->assertTrue(app(DepotCapacityChecker::class)->canCoverOutstanding($disaster));
    }

    public function test_false_when_depot_stock_falls_short_of_the_open_request(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();

        ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 500,
            'status' => 'unmet',
        ]);

        $depot = Depot::factory()->create(['status' => 'OPERATIONAL']);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 10,
            'quantity_reserved' => 0,
        ]);

        $this->assertFalse(app(DepotCapacityChecker::class)->canCoverOutstanding($disaster));
    }

    public function test_ignores_stock_at_a_non_operational_depot(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();

        ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 50,
            'status' => 'pending',
        ]);

        $depot = Depot::factory()->create(['status' => 'OFFLINE']);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 500,
            'quantity_reserved' => 0,
        ]);

        $this->assertFalse(app(DepotCapacityChecker::class)->canCoverOutstanding($disaster));
    }
}
