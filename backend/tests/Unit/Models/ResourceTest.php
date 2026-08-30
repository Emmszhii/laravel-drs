<?php

namespace Tests\Unit\Models;

use App\Models\Depot;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_quantities_to_int_and_expiry_to_date(): void
    {
        $resource = Resource::factory()->create([
            'quantity_available' => '100',
            'quantity_reserved' => '10',
            'expiry_date' => '2027-01-01',
        ]);

        $this->assertIsInt($resource->quantity_available);
        $this->assertIsInt($resource->quantity_reserved);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $resource->expiry_date);
    }

    public function test_depot_relation(): void
    {
        $depot = Depot::factory()->create();
        $resource = Resource::factory()->create(['depot_id' => $depot->id]);

        $this->assertTrue($resource->depot->is($depot));
    }

    public function test_resource_type_relation(): void
    {
        $resourceType = ResourceType::factory()->create();
        $resource = Resource::factory()->create(['resource_type_id' => $resourceType->id]);

        $this->assertTrue($resource->resourceType->is($resourceType));
    }
}
