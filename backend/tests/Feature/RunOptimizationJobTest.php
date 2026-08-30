<?php

namespace Tests\Feature;

use App\Jobs\RunDispatchPlanningJob;
use App\Jobs\RunOptimizationJob;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Resource;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Services\Optimization\OptimizationServiceClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Happy path: solver-service is reachable and returns a valid allocation +
 * routing response. Complements OptimizationFallbackTest (unreachable/error
 * paths) and RunDispatchPlanningJobTest's end-to-end wiring test (which uses
 * a placeholder vehicle id that never matches the lazily-created vehicle).
 */
class RunOptimizationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path_persists_run_and_dispatches_planning_job_with_real_routing_payload(): void
    {
        Queue::fake();

        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id, 'population_affected' => 50]);
        $depot = Depot::factory()->create();
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'quantity_available' => 100,
            'quantity_reserved' => 0,
        ]);
        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id, 'capacity' => 100]);

        Http::fake([
            '*api/v1/optimize/allocation' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 42.5,
                'allocations' => [
                    ['zone_id' => $zone->id, 'depot_id' => $depot->id, 'quantity' => 50],
                ],
            ]),
            '*api/v1/optimize/routing' => Http::response([
                'algorithm' => 'ortools_cvrptw',
                'objective_value' => 5.0,
                'routes' => [
                    [
                        'vehicle_id' => $vehicle->id,
                        'stops' => [
                            ['location_id' => $depot->id, 'arrival_time' => 0, 'quantity' => 0],
                            ['location_id' => $zone->id, 'arrival_time' => 10, 'quantity' => 50],
                            ['location_id' => $depot->id, 'arrival_time' => 20, 'quantity' => 0],
                        ],
                    ],
                ],
            ]),
        ]);

        app(RunOptimizationJob::class, ['disaster' => $disaster])
            ->handle(app(OptimizationServiceClient::class));

        // Real vehicle already existed on the depot, so optimizeRouting()
        // should not have lazily created another one.
        $this->assertDatabaseCount('app_vehicles', 1);

        $run = $disaster->optimizationRuns()->latest()->first();
        $this->assertNotNull($run);
        $this->assertSame('completed', $run->status);
        $this->assertSame('pulp_linear_programming', $run->algorithm_used);
        $this->assertEquals(42.5, $run->objective_value);
        $this->assertSame('ortools_cvrptw', $run->response_payload['routing']['algorithm']);

        Queue::assertPushed(RunDispatchPlanningJob::class, function (RunDispatchPlanningJob $job) use ($disaster, $vehicle) {
            if ($job->disaster->id !== $disaster->id) {
                return false;
            }

            $routes = $job->routingResult['routes'] ?? [];

            return $job->routingResult['algorithm'] === 'ortools_cvrptw'
                && count($routes) === 1
                && $routes[0]['vehicle_id'] === $vehicle->id;
        });

        Http::assertSent(function ($request) use ($vehicle, $depot) {
            if (! str_contains($request->url(), 'optimize/routing')) {
                return true;
            }

            $sentVehicle = collect($request->data()['vehicles'])->firstWhere('id', $vehicle->id);

            return $sentVehicle !== null && $sentVehicle['depot_id'] === $depot->id;
        });
    }
}
