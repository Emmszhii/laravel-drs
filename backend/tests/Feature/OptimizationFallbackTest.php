<?php

namespace Tests\Feature;

use App\Jobs\RunOptimizationJob;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Resource;
use App\Models\Zone;
use App\Services\Optimization\OptimizationServiceClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Solver-service outages must not fail RunOptimizationJob — see
 * OptimizationServiceClient::optimizeAllocation's greedy fallback.
 */
class OptimizationFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_completes_with_greedy_fallback_when_solver_service_is_unreachable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        app(RunOptimizationJob::class, ['disaster' => $disaster])
            ->handle(app(OptimizationServiceClient::class));

        $run = $disaster->optimizationRuns()->latest()->first();

        $this->assertNotNull($run);
        $this->assertSame('completed', $run->status);
        $this->assertStringStartsWith('greedy_', $run->algorithm_used);
    }

    public function test_job_completes_with_greedy_fallback_when_solver_service_errors(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'internal error'], 500),
        ]);

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        app(RunOptimizationJob::class, ['disaster' => $disaster])
            ->handle(app(OptimizationServiceClient::class));

        $run = $disaster->optimizationRuns()->latest()->first();

        $this->assertNotNull($run);
        $this->assertSame('completed', $run->status);
        $this->assertStringStartsWith('greedy_', $run->algorithm_used);
    }

    public function test_job_uses_solver_service_result_when_reachable(): void
    {
        Http::fake([
            '*' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 0,
                'allocations' => [],
            ]),
        ]);

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        app(RunOptimizationJob::class, ['disaster' => $disaster])
            ->handle(app(OptimizationServiceClient::class));

        $run = $disaster->optimizationRuns()->latest()->first();

        $this->assertSame('pulp_linear_programming', $run->algorithm_used);
    }

    public function test_depot_capacity_is_sent_to_the_solver_service(): void
    {
        Http::fake([
            '*' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 0,
                'allocations' => [],
            ]),
        ]);

        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);

        $depot = Depot::factory()->create();
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

        app(RunOptimizationJob::class, ['disaster' => $disaster])
            ->handle(app(OptimizationServiceClient::class));

        Http::assertSent(function ($request) use ($depot) {
            $sentDepot = collect($request->data()['depots'])
                ->firstWhere('id', $depot->id);

            // (100 - 30) + (50 - 10) = 110
            return $sentDepot !== null && $sentDepot['capacity'] === 110;
        });
    }
}
