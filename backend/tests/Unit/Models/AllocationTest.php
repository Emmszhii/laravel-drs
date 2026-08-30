<?php

namespace Tests\Unit\Models;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\OptimizationRun;
use App\Models\ResourceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $request = ResourceRequest::factory()->create();
        $depot = Depot::factory()->create();
        $run = OptimizationRun::factory()->create();

        $allocation = Allocation::create([
            'request_id' => $request->id,
            'depot_id' => $depot->id,
            'optimization_run_id' => $run->id,
            'quantity_allocated' => 42,
            'status' => 'pending',
        ]);

        $this->assertSame($request->id, $allocation->request_id);
        $this->assertSame($depot->id, $allocation->depot_id);
        $this->assertSame($run->id, $allocation->optimization_run_id);
        $this->assertSame(42, $allocation->quantity_allocated);
        $this->assertSame('pending', $allocation->status);
    }

    public function test_request_relation_resolves_to_the_owning_resource_request(): void
    {
        $request = ResourceRequest::factory()->create();
        $allocation = Allocation::factory()->create(['request_id' => $request->id]);

        $this->assertTrue($allocation->request->is($request));
    }

    public function test_depot_relation_resolves_to_the_owning_depot(): void
    {
        $depot = Depot::factory()->create();
        $allocation = Allocation::factory()->create(['depot_id' => $depot->id]);

        $this->assertTrue($allocation->depot->is($depot));
    }
}
