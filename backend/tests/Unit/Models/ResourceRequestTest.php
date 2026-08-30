<?php

namespace Tests\Unit\Models;

use App\Models\Allocation;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $zone = Zone::factory()->create();
        $resourceType = ResourceType::factory()->create();

        $request = ResourceRequest::create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 250,
            'priority' => 'high',
            'status' => 'pending',
            'requested_by' => null,
        ]);

        $this->assertSame(250, $request->quantity_requested);
        $this->assertSame('high', $request->priority);
        $this->assertSame('pending', $request->status);
    }

    public function test_zone_relation(): void
    {
        $zone = Zone::factory()->create();
        $request = ResourceRequest::factory()->create(['zone_id' => $zone->id]);

        $this->assertTrue($request->zone->is($zone));
    }

    public function test_resource_type_relation(): void
    {
        $resourceType = ResourceType::factory()->create();
        $request = ResourceRequest::factory()->create(['resource_type_id' => $resourceType->id]);

        $this->assertTrue($request->resourceType->is($resourceType));
    }

    public function test_allocations_relation_returns_only_this_requests_allocations(): void
    {
        $request = ResourceRequest::factory()->create();
        $otherRequest = ResourceRequest::factory()->create();

        $allocation = Allocation::factory()->create(['request_id' => $request->id]);
        Allocation::factory()->create(['request_id' => $otherRequest->id]);

        $this->assertCount(1, $request->allocations);
        $this->assertTrue($request->allocations->first()->is($allocation));
    }
}
