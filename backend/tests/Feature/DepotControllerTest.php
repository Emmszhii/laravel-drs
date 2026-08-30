<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_seeded_depots_with_expected_fields(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create([
            'name' => 'Laguna Central Depot',
            'address' => '123 Rizal St, Sta Cruz',
            'status' => 'OPERATIONAL',
            'lat' => 14.2,
            'lng' => 121.4,
            'contact_first_name' => 'Juan',
            'contact_last_name' => 'Dela Cruz',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '+63 9123456789',
        ]);

        $response = $this->actingAs($user)->getJson('/api/depots');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment([
            'id' => $depot->id,
            'name' => 'Laguna Central Depot',
            'address' => '123 Rizal St, Sta Cruz',
            'status' => 'OPERATIONAL',
            'contact_first_name' => 'Juan',
            'contact_last_name' => 'Dela Cruz',
            'contact_email' => 'juan@example.com',
        ]);
    }

    public function test_pagination_search_returns_paginated_shape(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Depot::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/depots/pagination-search?per_page=2&page=1');

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

    public function test_pagination_search_filters_by_search_term(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $target = Depot::factory()->create(['name' => 'Alaminos Relief Depot']);
        Depot::factory()->create(['name' => 'Cavite Storage Facility']);

        $response = $this->actingAs($user)->getJson('/api/depots/pagination-search?search=Alaminos');

        $response->assertOk();
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
        $this->assertSame('Alaminos Relief Depot', $items[0]['name']);
    }

    public function test_store_creates_depot_with_submitted_values_and_returns_them(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'Binan Emergency Depot',
            'address' => '45 Mabini Ave, Binan',
            'status' => 'OPERATIONAL',
            'lat' => 14.3383,
            'lng' => 121.0809,
            'contact_first_name' => 'Maria',
            'contact_last_name' => 'Santos',
            'contact_email' => 'maria.santos@example.com',
            'contact_phone' => '+63 9987654321',
        ];

        $response = $this->actingAs($user)->postJson('/api/depots', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Binan Emergency Depot');
        $response->assertJsonPath('data.address', '45 Mabini Ave, Binan');
        $response->assertJsonPath('data.status', 'OPERATIONAL');
        $response->assertJsonPath('data.contact_email', 'maria.santos@example.com');

        $this->assertDatabaseHas('app_depots', [
            'name' => 'Binan Emergency Depot',
            'address' => '45 Mabini Ave, Binan',
            'status' => 'OPERATIONAL',
            'contact_first_name' => 'Maria',
            'contact_last_name' => 'Santos',
            'contact_email' => 'maria.santos@example.com',
            'contact_phone' => '+63 9987654321',
        ]);
    }

    public function test_store_fails_validation_when_required_field_missing(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->postJson('/api/depots', [
            'address' => 'Missing name field',
            'status' => 'OPERATIONAL',
            'lat' => 14.1,
            'lng' => 121.1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_validation_for_invalid_status(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->postJson('/api/depots', [
            'name' => 'Bad Status Depot',
            'address' => '1 Test St',
            'status' => 'NOT_A_STATUS',
            'lat' => 14.1,
            'lng' => 121.1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_update_persists_changed_fields_and_returns_updated_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create([
            'name' => 'Old Depot Name',
            'status' => 'OPERATIONAL',
        ]);

        $response = $this->actingAs($user)->postJson("/api/depots/{$depot->id}", [
            'name' => 'Renamed Depot',
            'status' => 'LIMITED CAPACITY',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Renamed Depot');
        $response->assertJsonPath('data.status', 'LIMITED CAPACITY');

        $depot->refresh();
        $this->assertSame('Renamed Depot', $depot->name);
        $this->assertSame('LIMITED CAPACITY', $depot->status);
    }

    public function test_destroy_removes_depot_row(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/depots/{$depot->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('app_depots', ['id' => $depot->id]);
    }

    public function test_destroy_is_blocked_when_depot_has_resources_in_use(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $depot = Depot::factory()->create();
        Resource::factory()->create(['depot_id' => $depot->id]);

        $response = $this->actingAs($user)->deleteJson("/api/depots/{$depot->id}");

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('errors.in_use.0.label', 'Resources');
        $this->assertDatabaseHas('app_depots', ['id' => $depot->id]);
    }
}
