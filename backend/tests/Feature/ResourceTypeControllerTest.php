<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_seeded_resource_types_with_expected_fields(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create([
            'name' => 'Medical kits',
            'unit' => 'kits',
        ]);

        $response = $this->actingAs($user)->getJson('/api/resource-types');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment([
            'id' => $resourceType->id,
            'name' => 'Medical kits',
            'unit' => 'kits',
        ]);
    }

    public function test_pagination_search_returns_paginated_shape(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        ResourceType::factory()->createMany([
            ['name' => 'Type A', 'unit' => 'units'],
            ['name' => 'Type B', 'unit' => 'units'],
            ['name' => 'Type C', 'unit' => 'units'],
        ]);

        $response = $this->actingAs($user)->getJson('/api/resource-types/pagination-search?per_page=2&page=1');

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

    public function test_pagination_search_filters_by_name(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $target = ResourceType::factory()->create(['name' => 'Portable generators', 'unit' => 'units']);
        ResourceType::factory()->create(['name' => 'Sleeping mats', 'unit' => 'pieces']);

        $response = $this->actingAs($user)->getJson('/api/resource-types/pagination-search?search=generators');

        $response->assertOk();
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
        $this->assertSame('Portable generators', $items[0]['name']);
    }

    public function test_store_creates_resource_type_with_submitted_values_and_returns_them(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->postJson('/api/resource-types', [
            'name' => 'Water filters',
            'unit' => 'units',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Water filters');
        $response->assertJsonPath('data.unit', 'units');

        $this->assertDatabaseHas('app_resource_types', [
            'name' => 'Water filters',
            'unit' => 'units',
        ]);
    }

    public function test_store_fails_validation_when_required_field_missing(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->postJson('/api/resource-types', [
            'unit' => 'units',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_validation_when_name_is_not_unique(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        ResourceType::factory()->create(['name' => 'Duplicate Kits']);

        $response = $this->actingAs($user)->postJson('/api/resource-types', [
            'name' => 'Duplicate Kits',
            'unit' => 'kits',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_persists_changed_fields_and_returns_updated_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create([
            'name' => 'Old Name',
            'unit' => 'pieces',
        ]);

        $response = $this->actingAs($user)->postJson("/api/resource-types/{$resourceType->id}", [
            'name' => 'New Name',
            'unit' => 'kits',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');
        $response->assertJsonPath('data.unit', 'kits');

        $resourceType->refresh();
        $this->assertSame('New Name', $resourceType->name);
        $this->assertSame('kits', $resourceType->unit);
    }

    public function test_update_allows_keeping_same_name_on_the_same_record(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create([
            'name' => 'Stable Name',
            'unit' => 'pieces',
        ]);

        $response = $this->actingAs($user)->postJson("/api/resource-types/{$resourceType->id}", [
            'name' => 'Stable Name',
            'unit' => 'kits',
        ]);

        $response->assertOk();
        $resourceType->refresh();
        $this->assertSame('Stable Name', $resourceType->name);
        $this->assertSame('kits', $resourceType->unit);
    }

    public function test_destroy_removes_resource_type_row(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/resource-types/{$resourceType->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('app_resource_types', ['id' => $resourceType->id]);
    }

    public function test_destroy_is_blocked_when_resource_type_has_resources_in_use(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $resourceType = ResourceType::factory()->create();
        Resource::factory()->create(['resource_type_id' => $resourceType->id]);

        $response = $this->actingAs($user)->deleteJson("/api/resource-types/{$resourceType->id}");

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('errors.in_use.0.label', 'Resources');
        $this->assertDatabaseHas('app_resource_types', ['id' => $resourceType->id]);
    }
}
