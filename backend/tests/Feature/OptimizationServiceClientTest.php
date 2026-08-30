<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Resource;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Services\Optimization\OptimizationServiceClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Dedicated coverage of OptimizationServiceClient itself (payload shapes,
 * vehicle lazy-creation) — OptimizationFallbackTest and
 * RunDispatchPlanningJobTest exercise it only indirectly via the job.
 */
class OptimizationServiceClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_optimize_allocation_posts_the_expected_payload_shape(): void
    {
        Http::fake([
            '*api/v1/optimize/allocation' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 0,
                'allocations' => [],
            ]),
        ]);

        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'lat' => 14.2,
            'lng' => 121.1,
            'population_affected' => 500,
        ]);

        $depot = Depot::factory()->create(['lat' => 14.5, 'lng' => 121.3]);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'quantity_available' => 100,
            'quantity_reserved' => 30,
        ]);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'quantity_available' => 50,
            'quantity_reserved' => 10,
        ]);

        $client = app(OptimizationServiceClient::class);
        $result = $client->optimizeAllocation($disaster->fresh(['zones']));

        $this->assertSame('pulp_linear_programming', $result['algorithm']);

        Http::assertSent(function ($request) use ($disaster, $zone, $depot) {
            $data = $request->data();

            if ($data['disaster_id'] !== $disaster->id) {
                return false;
            }

            $sentZone = collect($data['zones'])->firstWhere('id', $zone->id);
            if ($sentZone === null
                || $sentZone['lat'] != 14.2
                || $sentZone['lng'] != 121.1
                || $sentZone['demand'] != 500
            ) {
                return false;
            }

            $sentDepot = collect($data['depots'])->firstWhere('id', $depot->id);

            // (100 - 30) + (50 - 10) = 110
            return $sentDepot !== null
                && $sentDepot['lat'] == 14.5
                && $sentDepot['lng'] == 121.3
                && $sentDepot['capacity'] === 110;
        });
    }

    public function test_optimize_routing_posts_the_expected_payload_shape_and_creates_a_default_vehicle(): void
    {
        Http::fake([
            '*api/v1/optimize/routing' => Http::response([
                'algorithm' => 'ortools_cvrptw',
                'objective_value' => 5.0,
                'routes' => [],
            ]),
        ]);

        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'lat' => 14.2,
            'lng' => 121.1,
            'population_affected' => 500,
        ]);

        $depot = Depot::factory()->create(['lat' => 14.5, 'lng' => 121.3]);
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'quantity_available' => 100,
            'quantity_reserved' => 20,
        ]);

        $this->assertDatabaseCount('app_vehicles', 0);

        $allocations = [
            ['zone_id' => $zone->id, 'depot_id' => $depot->id, 'quantity' => 80],
        ];

        $client = app(OptimizationServiceClient::class);
        $result = $client->optimizeRouting($disaster->fresh(['zones']), $allocations);

        $this->assertSame('ortools_cvrptw', $result['algorithm']);

        // A default vehicle is lazily created and persisted for the depot,
        // since it had none.
        $this->assertDatabaseCount('app_vehicles', 1);
        $vehicle = Vehicle::firstOrFail();
        $this->assertSame($depot->id, $vehicle->depot_id);
        $this->assertSame('Vehicle 1', $vehicle->label);
        $this->assertSame('available', $vehicle->status);
        $this->assertSame(80, $vehicle->capacity);

        Http::assertSent(function ($request) use ($disaster, $depot, $vehicle, $allocations) {
            $data = $request->data();

            if ($data['disaster_id'] !== $disaster->id || $data['allocations'] !== $allocations) {
                return false;
            }

            $sentDepot = collect($data['depots'])->firstWhere('id', $depot->id);
            if ($sentDepot === null || $sentDepot['capacity'] !== 80) {
                return false;
            }

            $sentVehicle = collect($data['vehicles'])->firstWhere('id', $vehicle->id);

            return $sentVehicle !== null
                && $sentVehicle['depot_id'] === $depot->id
                && $sentVehicle['capacity'] === 80;
        });
    }

    public function test_optimize_routing_reuses_an_existing_vehicle_instead_of_creating_one(): void
    {
        Http::fake([
            '*api/v1/optimize/routing' => Http::response([
                'algorithm' => 'ortools_cvrptw',
                'objective_value' => 0,
                'routes' => [],
            ]),
        ]);

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);
        $depot = Depot::factory()->create();
        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id, 'capacity' => 250]);

        $client = app(OptimizationServiceClient::class);
        $client->optimizeRouting($disaster->fresh(['zones']), []);

        $this->assertDatabaseCount('app_vehicles', 1);

        Http::assertSent(function ($request) use ($vehicle) {
            $sentVehicle = collect($request->data()['vehicles'])->firstWhere('id', $vehicle->id);

            return $sentVehicle !== null && $sentVehicle['capacity'] === 250;
        });
    }

    public function test_optimize_allocation_falls_back_on_connection_failure(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        $client = app(OptimizationServiceClient::class);
        $result = $client->optimizeAllocation($disaster->fresh(['zones']));

        $this->assertStringStartsWith('greedy_', $result['algorithm']);
    }

    public function test_optimize_routing_returns_empty_routes_on_connection_failure(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        $client = app(OptimizationServiceClient::class);
        $result = $client->optimizeRouting($disaster->fresh(['zones']), []);

        $this->assertSame('unavailable', $result['algorithm']);
        $this->assertSame([], $result['routes']);
    }

    public function test_optimize_routing_returns_empty_routes_on_a_failed_response(): void
    {
        Http::fake([
            '*api/v1/optimize/routing' => Http::response(['message' => 'error'], 500),
        ]);

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        $client = app(OptimizationServiceClient::class);
        $result = $client->optimizeRouting($disaster->fresh(['zones']), []);

        $this->assertSame('unavailable', $result['algorithm']);
        $this->assertSame([], $result['routes']);
    }
}
