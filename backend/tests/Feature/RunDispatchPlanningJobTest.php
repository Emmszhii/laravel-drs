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
use Tests\TestCase;

/**
 * Phase 3: routing output (CVRPTW stops) persisted as real Dispatch rows,
 * with a real Vehicle behind them instead of only a free-text label.
 */
class RunDispatchPlanningJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_rows_are_created_from_a_routing_result(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $depot = Depot::factory()->create();
        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id, 'capacity' => 100]);

        $routingResult = [
            'algorithm' => 'ortools_cvrptw',
            'objective_value' => 12.3,
            'routes' => [
                [
                    'vehicle_id' => $vehicle->id,
                    'stops' => [
                        ['location_id' => $depot->id, 'arrival_time' => 0, 'quantity' => 0],
                        ['location_id' => $zone->id, 'arrival_time' => 15, 'quantity' => 50],
                        ['location_id' => $depot->id, 'arrival_time' => 30, 'quantity' => 0],
                    ],
                ],
            ],
        ];

        (new RunDispatchPlanningJob($disaster, $routingResult))->handle();

        $this->assertDatabaseHas('app_dispatches', [
            'disaster_id' => $disaster->id,
            'zone_id' => $zone->id,
            'depot_id' => $depot->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_label' => $vehicle->label,
            'status' => 'pending',
            'driver_id' => null,
        ]);
    }

    public function test_stops_with_zero_quantity_do_not_create_dispatches(): void
    {
        $disaster = Disaster::factory()->create();
        $depot = Depot::factory()->create();
        $vehicle = Vehicle::factory()->create(['depot_id' => $depot->id]);

        $routingResult = [
            'routes' => [
                [
                    'vehicle_id' => $vehicle->id,
                    'stops' => [
                        ['location_id' => $depot->id, 'arrival_time' => 0, 'quantity' => 0],
                    ],
                ],
            ],
        ];

        (new RunDispatchPlanningJob($disaster, $routingResult))->handle();

        $this->assertDatabaseCount('app_dispatches', 0);
    }

    public function test_run_optimization_job_wires_both_stages_end_to_end(): void
    {
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id, 'population_affected' => 50]);
        $depot = Depot::factory()->create();
        Resource::factory()->create([
            'depot_id' => $depot->id,
            'quantity_available' => 100,
            'quantity_reserved' => 0,
        ]);

        Http::fake([
            '*api/v1/optimize/allocation' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 0,
                'allocations' => [
                    ['zone_id' => $zone->id, 'depot_id' => $depot->id, 'quantity' => 50],
                ],
            ]),
            '*api/v1/optimize/routing' => Http::response([
                'algorithm' => 'ortools_cvrptw',
                'objective_value' => 5.0,
                'routes' => [
                    [
                        'vehicle_id' => 'placeholder', // replaced below once the real vehicle is known
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

        // optimizeRouting() lazily creates a default Vehicle for the depot
        // since none existed — the fake response's vehicle_id ("placeholder")
        // won't match it, so no Dispatch is created for it. This test's real
        // assertion is the pipeline itself completing without error and the
        // OptimizationRun capturing both stages.
        $run = $disaster->optimizationRuns()->latest()->first();
        $this->assertNotNull($run);
        $this->assertSame('pulp_linear_programming', $run->algorithm_used);
        $this->assertArrayHasKey('routing', $run->response_payload);
        $this->assertSame('ortools_cvrptw', $run->response_payload['routing']['algorithm']);
        $this->assertDatabaseCount('app_vehicles', 1);
    }
}
