<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvacuationCenterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_persisted_evacuation_centers(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $center = EvacuationCenter::factory()->create(['name' => 'Legazpi Gym']);

        $response = $this->actingAs($coordinator)->getJson('/api/evacuation-centers');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['id' => $center->id, 'name' => 'Legazpi Gym']);
    }

    public function test_pagination_search_returns_matching_center(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        EvacuationCenter::factory()->create(['name' => 'Marikina Covered Court']);
        EvacuationCenter::factory()->create(['name' => 'Tacloban Elementary School']);

        $response = $this->actingAs($coordinator)
            ->getJson('/api/evacuation-centers/pagination-search?search=Marikina&per_page=10&page=1');

        $response->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonFragment(['name' => 'Marikina Covered Court']);
    }

    public function test_coordinator_can_store_an_evacuation_center(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $payload = [
            'disaster_id' => $disaster->id,
            'name' => 'Itogon Multi-Purpose Hall',
            'address' => 'Itogon, Benguet',
            'lat' => 16.3667,
            'lng' => 120.6833,
            'capacity' => 500,
            'families_served' => 20,
            'individuals_served' => 80,
        ];

        $response = $this->actingAs($coordinator)->postJson('/api/evacuation-centers', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Itogon Multi-Purpose Hall')
            ->assertJsonPath('data.status', 'OPEN');

        $this->assertDatabaseHas('app_evacuation_centers', [
            'disaster_id' => $disaster->id,
            'name' => 'Itogon Multi-Purpose Hall',
            'status' => 'OPEN',
            'capacity' => 500,
        ]);
    }

    public function test_store_defaults_status_to_open_when_not_submitted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/evacuation-centers', [
            'disaster_id' => $disaster->id,
            'name' => 'Cagayan de Oro Sports Complex',
            'lat' => 8.4822,
            'lng' => 124.6472,
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'OPEN');
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/evacuation-centers', [
            'name' => 'Missing Disaster Link',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['disaster_id', 'lat', 'lng']);
    }

    public function test_respondent_cannot_store_an_evacuation_center(): void
    {
        $respondent = User::factory()->create(['role' => 'respondent']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($respondent)->postJson('/api/evacuation-centers', [
            'disaster_id' => $disaster->id,
            'name' => 'Blocked Center',
            'lat' => 13.1,
            'lng' => 123.7,
        ]);

        $response->assertStatus(403);
    }

    public function test_coordinator_can_update_an_evacuation_center(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $center = EvacuationCenter::factory()->create([
            'name' => 'Old Name',
            'capacity' => 100,
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($coordinator)->postJson("/api/evacuation-centers/{$center->id}", [
            'name' => 'Updated Evacuation Center',
            'capacity' => 250,
            'status' => 'CLOSED',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Evacuation Center')
            ->assertJsonPath('data.status', 'CLOSED')
            ->assertJsonPath('data.capacity', 250);

        $this->assertDatabaseHas('app_evacuation_centers', [
            'id' => $center->id,
            'name' => 'Updated Evacuation Center',
            'status' => 'CLOSED',
            'capacity' => 250,
        ]);
    }

    public function test_update_validation_fails_on_invalid_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $center = EvacuationCenter::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/evacuation-centers/{$center->id}", [
            'status' => 'DEMOLISHED',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    public function test_admin_can_destroy_an_evacuation_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $center = EvacuationCenter::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/evacuation-centers/{$center->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('app_evacuation_centers', ['id' => $center->id]);
    }

    public function test_respondent_cannot_destroy_an_evacuation_center(): void
    {
        $respondent = User::factory()->create(['role' => 'respondent']);
        $center = EvacuationCenter::factory()->create();

        $response = $this->actingAs($respondent)->deleteJson("/api/evacuation-centers/{$center->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('app_evacuation_centers', ['id' => $center->id]);
    }
}
