<?php

namespace Tests\Unit;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Zone;
use App\Services\Gis\GeofenceService;
use App\Services\Optimization\GreedyAllocationSolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreedyAllocationSolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocations_never_exceed_depot_capacity(): void
    {
        $disaster = Disaster::factory()->create();

        $nearDepot = Depot::factory()->create(['lat' => 13.0, 'lng' => 123.0]);
        $farDepot = Depot::factory()->create(['lat' => 8.0, 'lng' => 125.0]);
        $resourceType = ResourceType::factory()->create();

        Resource::factory()->create([
            'depot_id' => $nearDepot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 100,
        ]);
        Resource::factory()->create([
            'depot_id' => $farDepot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 50,
        ]);

        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'lat' => 13.01,
            'lng' => 123.01,
            'population_affected' => 300,
        ]);

        $result = app(GreedyAllocationSolver::class)->allocate($disaster);

        $this->assertSame('greedy_nearest_depot_fallback', $result['algorithm']);

        $totalAllocated = collect($result['allocations'])->sum('quantity');
        $this->assertSame(150, $totalAllocated); // capped at combined capacity (100 + 50)
        $this->assertSame(150.0, $result['objective_value']); // 300 demand - 150 fulfilled = 150 unmet

        foreach ($result['allocations'] as $line) {
            $this->assertLessThanOrEqual(
                $line['depot_id'] === $nearDepot->id ? 100 : 50,
                $line['quantity'],
            );
        }
    }

    public function test_nearest_depot_is_served_first(): void
    {
        $disaster = Disaster::factory()->create();

        $nearDepot = Depot::factory()->create(['lat' => 13.0, 'lng' => 123.0]);
        $farDepot = Depot::factory()->create(['lat' => 8.0, 'lng' => 125.0]);
        $resourceType = ResourceType::factory()->create();

        Resource::factory()->create([
            'depot_id' => $nearDepot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 1000,
        ]);
        Resource::factory()->create([
            'depot_id' => $farDepot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 1000,
        ]);

        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'lat' => 13.01,
            'lng' => 123.01,
            'population_affected' => 100,
        ]);

        $result = app(GreedyAllocationSolver::class)->allocate($disaster);

        $this->assertCount(1, $result['allocations']);
        $this->assertSame($nearDepot->id, $result['allocations'][0]['depot_id']);
        $this->assertSame(100, $result['allocations'][0]['quantity']);
        $this->assertSame(0.0, $result['objective_value']);
    }
}
