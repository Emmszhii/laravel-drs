<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers RequestController::index/paginationSearch. store is covered by
 * IdempotentCreateTest — not duplicated here.
 */
class RequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_requests_with_zone_disaster_and_resource_type(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $disaster = Disaster::factory()->create(['name' => 'Typhoon Test']);
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id, 'name' => 'Brgy. Uno']);
        $resourceType = ResourceType::factory()->create(['name' => 'Medical kits']);
        $requestRow = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 75,
            'priority' => 'critical',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($viewer)->getJson('/api/requests');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.id', $requestRow->id);
        $response->assertJsonPath('data.0.quantity_requested', 75);
        $response->assertJsonPath('data.0.priority', 'critical');
        $response->assertJsonPath('data.0.status', 'pending');
        $response->assertJsonPath('data.0.zone.id', $zone->id);
        $response->assertJsonPath('data.0.zone.name', 'Brgy. Uno');
        $response->assertJsonPath('data.0.zone.disaster.id', $disaster->id);
        $response->assertJsonPath('data.0.zone.disaster.name', 'Typhoon Test');
        $response->assertJsonPath('data.0.resource_type.id', $resourceType->id);
        $response->assertJsonPath('data.0.resource_type.name', 'Medical kits');
    }

    public function test_index_orders_by_created_at_descending(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $older = ResourceRequest::factory()->create(['created_at' => now()->subDay()]);
        $newer = ResourceRequest::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($viewer)->getJson('/api/requests');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $newer->id);
        $response->assertJsonPath('data.1.id', $older->id);
    }

    public function test_pagination_search_filters_by_zone_name(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $targetZone = Zone::factory()->create(['name' => 'Brgy. Malinis']);
        $otherZone = Zone::factory()->create(['name' => 'Brgy. Iba']);
        $match = ResourceRequest::factory()->create(['zone_id' => $targetZone->id]);
        ResourceRequest::factory()->create(['zone_id' => $otherZone->id]);

        $response = $this->actingAs($viewer)->getJson('/api/requests/pagination-search?search=Malinis&per_page=10&page=1');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_filters_by_resource_type_name(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $targetType = ResourceType::factory()->create(['name' => 'Portable generators']);
        $otherType = ResourceType::factory()->create(['name' => 'Blankets']);
        $match = ResourceRequest::factory()->create(['resource_type_id' => $targetType->id]);
        ResourceRequest::factory()->create(['resource_type_id' => $otherType->id]);

        $response = $this->actingAs($viewer)->getJson('/api/requests/pagination-search?search=generators&per_page=10&page=1');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_filters_by_exact_status(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $match = ResourceRequest::factory()->create(['status' => 'fulfilled']);
        ResourceRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($viewer)->getJson('/api/requests/pagination-search?'.http_build_query([
            'filters' => ['status' => 'fulfilled'],
            'per_page' => 10,
            'page' => 1,
        ]));

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_returns_correct_pagination_meta(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        ResourceRequest::factory()->count(3)->create();

        $response = $this->actingAs($viewer)->getJson('/api/requests/pagination-search?per_page=2&page=1');

        $response->assertOk();
        $response->assertJsonPath('data.meta.current_page', 1);
        $response->assertJsonPath('data.meta.per_page', 2);
        $response->assertJsonPath('data.meta.total', 3);
        $response->assertJsonPath('data.meta.last_page', 2);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_pagination_search_sorts_by_quantity_requested(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $small = ResourceRequest::factory()->create(['quantity_requested' => 10]);
        $large = ResourceRequest::factory()->create(['quantity_requested' => 900]);

        $response = $this->actingAs($viewer)->getJson('/api/requests/pagination-search?sort_by=quantity_requested&sort_dir=desc&per_page=10&page=1');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertSame($large->id, $data[0]['id']);
        $this->assertSame($small->id, $data[1]['id']);
    }
}
