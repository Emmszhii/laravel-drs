<?php

namespace Tests\Unit\Models;

use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $resourceType = ResourceType::create([
            'name' => 'Medical kits',
            'unit' => 'kits',
        ]);

        $this->assertSame('Medical kits', $resourceType->name);
        $this->assertSame('kits', $resourceType->unit);
    }

    public function test_resources_relation_returns_only_this_types_resources(): void
    {
        $resourceType = ResourceType::factory()->create();
        $otherType = ResourceType::factory()->create();

        $resource = Resource::factory()->create(['resource_type_id' => $resourceType->id]);
        Resource::factory()->create(['resource_type_id' => $otherType->id]);

        $this->assertCount(1, $resourceType->resources);
        $this->assertTrue($resourceType->resources->first()->is($resource));
    }

    public function test_requests_relation_returns_only_this_types_requests(): void
    {
        $resourceType = ResourceType::factory()->create();
        $otherType = ResourceType::factory()->create();

        $request = ResourceRequest::factory()->create(['resource_type_id' => $resourceType->id]);
        ResourceRequest::factory()->create(['resource_type_id' => $otherType->id]);

        $this->assertCount(1, $resourceType->requests);
        $this->assertTrue($resourceType->requests->first()->is($request));
    }
}
