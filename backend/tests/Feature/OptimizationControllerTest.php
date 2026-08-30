<?php

namespace Tests\Feature;

use App\Jobs\RunOptimizationJob;
use App\Models\Disaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Covers what Web\OptimizationController::trigger actually does — dispatch
 * RunOptimizationJob for the right disaster and redirect back with a status
 * flash message. Role-gating itself is covered by
 * RoleGatingTest::test_optimization_trigger_is_gated_by_role.
 */
class OptimizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_trigger_dispatches_run_optimization_job_for_the_disaster(): void
    {
        Queue::fake();

        $user = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $this->actingAs($user)->post("/incidents/{$disaster->id}/optimize");

        Queue::assertPushed(RunOptimizationJob::class, function ($job) use ($disaster) {
            return $job->disaster->id === $disaster->id;
        });
    }

    public function test_trigger_redirects_back_with_a_status_flash_message(): void
    {
        Queue::fake();

        $user = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create(['name' => 'Typhoon Test']);

        $response = $this->actingAs($user)
            ->from("/incidents/{$disaster->id}")
            ->post("/incidents/{$disaster->id}/optimize");

        $response->assertRedirect("/incidents/{$disaster->id}");
        $response->assertSessionHas('status', 'Optimization run queued for Typhoon Test.');
    }

    /**
     * QUEUE_CONNECTION=sync in phpunit.xml means, absent Queue::fake(), the
     * job runs synchronously in-process — exercising the real
     * OptimizationServiceClient HTTP call. Http::fake() stubs the
     * solver-service so this doesn't hit a real network service, and lets us
     * assert the resulting OptimizationRun row the job actually persists.
     */
    public function test_trigger_synchronously_runs_the_job_and_persists_an_optimization_run_when_queue_is_sync(): void
    {
        Http::fake([
            '*api/v1/optimize/allocation' => Http::response([
                'algorithm' => 'pulp_linear_programming',
                'objective_value' => 0,
                'allocations' => [],
            ]),
        ]);

        $user = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($user)->post("/incidents/{$disaster->id}/optimize");

        $response->assertRedirect();

        $run = $disaster->optimizationRuns()->latest()->first();
        $this->assertNotNull($run);
        $this->assertSame('completed', $run->status);
        $this->assertSame('pulp_linear_programming', $run->algorithm_used);
    }

    public function test_trigger_returns_404_for_a_nonexistent_disaster(): void
    {
        Queue::fake();

        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post('/incidents/00000000-0000-0000-0000-000000000000/optimize')
            ->assertStatus(404);
    }
}
