<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_seeded_resources_with_relations_loaded(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create(['name' => 'Main Depot']);
        $resourceType = ResourceType::factory()->create(['name' => 'Blankets', 'unit' => 'pieces']);
        $resource = Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 250,
            'quantity_reserved' => 30,
        ]);

        $response = $this->actingAs($user)->getJson('/api/resources');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment([
            'id' => $resource->id,
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 250,
            'quantity_reserved' => 30,
        ]);
        $response->assertJsonPath('data.0.depot.name', 'Main Depot');
        $response->assertJsonPath('data.0.resource_type.name', 'Blankets');
    }

    public function test_pagination_search_returns_paginated_shape(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Resource::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/resources/pagination-search?per_page=2&page=1');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ],
        ]);
        $response->assertJsonPath('data.meta.per_page', 2);
        $response->assertJsonPath('data.meta.total', 3);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_pagination_search_filters_by_depot_name(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $matchingDepot = Depot::factory()->create(['name' => 'Unique Sta Rosa Depot']);
        $otherDepot = Depot::factory()->create(['name' => 'Other Depot']);
        $target = Resource::factory()->create(['depot_id' => $matchingDepot->id]);
        Resource::factory()->create(['depot_id' => $otherDepot->id]);

        $response = $this->actingAs($user)->getJson('/api/resources/pagination-search?search=Sta Rosa');

        $response->assertOk();
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    public function test_pagination_search_filters_by_exact_depot_id(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depotA = Depot::factory()->create();
        $depotB = Depot::factory()->create();
        $target = Resource::factory()->create(['depot_id' => $depotA->id]);
        Resource::factory()->create(['depot_id' => $depotB->id]);

        $response = $this->actingAs($user)->getJson("/api/resources/pagination-search?filters[depot_id]={$depotA->id}");

        $response->assertOk();
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    public function test_store_creates_resource_with_submitted_values_and_returns_them(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();

        $payload = [
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 500,
            'quantity_reserved' => 50,
            'expiry_date' => '2027-01-15',
        ];

        $response = $this->actingAs($user)->postJson('/api/resources', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.depot_id', $depot->id);
        $response->assertJsonPath('data.resource_type_id', $resourceType->id);
        $response->assertJsonPath('data.quantity_available', 500);
        $response->assertJsonPath('data.quantity_reserved', 50);

        $this->assertDatabaseHas('app_resources', [
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 500,
            'quantity_reserved' => 50,
        ]);
    }

    public function test_store_fails_validation_when_required_field_missing(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/resources', [
            'depot_id' => $depot->id,
            'quantity_available' => 10,
            'quantity_reserved' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resource_type_id']);
    }

    public function test_store_fails_validation_for_nonexistent_depot(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/resources', [
            'depot_id' => '00000000-0000-0000-0000-000000000000',
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 10,
            'quantity_reserved' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['depot_id']);
    }

    public function test_update_persists_changed_fields_and_returns_updated_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resource = Resource::factory()->create([
            'quantity_available' => 100,
            'quantity_reserved' => 10,
        ]);

        $response = $this->actingAs($user)->postJson("/api/resources/{$resource->id}", [
            'quantity_available' => 300,
            'quantity_reserved' => 20,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.quantity_available', 300);
        $response->assertJsonPath('data.quantity_reserved', 20);

        $resource->refresh();
        $this->assertSame(300, $resource->quantity_available);
        $this->assertSame(20, $resource->quantity_reserved);
    }

    public function test_destroy_removes_resource_row(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/resources/{$resource->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('app_resources', ['id' => $resource->id]);
    }
}
