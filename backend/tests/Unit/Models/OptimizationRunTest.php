<?php

namespace Tests\Unit\Models;

use App\Models\Allocation;
use App\Models\Disaster;
use App\Models\OptimizationRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizationRunTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_payload_casts_to_array_and_objective_value_to_float(): void
    {
        $run = OptimizationRun::factory()->create([
            'objective_value' => '123.45',
            'response_payload' => ['foo' => 'bar'],
        ]);

        $this->assertIsFloat($run->objective_value);
        $this->assertSame(123.45, $run->objective_value);
        $this->assertIsArray($run->response_payload);
        $this->assertSame(['foo' => 'bar'], $run->response_payload);
    }

    public function test_disaster_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $run = OptimizationRun::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue($run->disaster->is($disaster));
    }

    public function test_allocations_relation_returns_only_this_runs_allocations(): void
    {
        $run = OptimizationRun::factory()->create();
        $otherRun = OptimizationRun::factory()->create();

        $allocation = Allocation::factory()->create(['optimization_run_id' => $run->id]);
        Allocation::factory()->create(['optimization_run_id' => $otherRun->id]);

        $this->assertCount(1, $run->allocations);
        $this->assertTrue($run->allocations->first()->is($allocation));
    }
}
