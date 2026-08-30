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
 * Covers IncidentController::show/update/updateArea/destroy/destroyArea.
 * store/storeArea/paginationSearch are covered by IdempotentCreateTest and
 * RoleGatingTest — not duplicated here.
 */
class IncidentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_the_disaster_with_its_zones(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create([
            'name' => 'Typhoon Test',
            'type' => 'Typhoon',
            'signal' => 3,
        ]);
        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'name' => 'Brgy. Test Zone',
        ]);

        $response = $this->actingAs($coordinator)->getJson("/api/incidents/{$disaster->id}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $disaster->id);
        $response->assertJsonPath('data.name', 'Typhoon Test');
        $response->assertJsonPath('data.signal', 3);
        $response->assertJsonPath('data.zones.0.id', $zone->id);
        $response->assertJsonPath('data.zones.0.name', 'Brgy. Test Zone');
    }

    public function test_show_includes_the_disasters_requests_via_zones(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();
        $request = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 42,
        ]);

        $response = $this->actingAs($coordinator)->getJson("/api/incidents/{$disaster->id}");

        $response->assertOk();
        $response->assertJsonPath('data.requests.0.id', $request->id);
        $response->assertJsonPath('data.requests.0.quantity_requested', 42);
    }

    public function test_show_returns_404_for_missing_incident(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);

        $this->actingAs($coordinator)->getJson('/api/incidents/'.\Illuminate\Support\Str::uuid())
            ->assertStatus(404);
    }

    public function test_update_persists_changed_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create([
            'name' => 'Original Name',
            'signal' => 1,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/incidents/{$disaster->id}", [
            'name' => 'Updated Name',
            'signal' => 4,
            'status' => 'MONITORING',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Record updated successfully.');
        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.signal', 4);
        $response->assertJsonPath('data.status', 'MONITORING');

        $fresh = $disaster->fresh();
        $this->assertSame('Updated Name', $fresh->name);
        $this->assertSame(4, $fresh->signal);
        $this->assertSame('MONITORING', $fresh->status);
    }

    public function test_update_fails_validation_for_invalid_signal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create(['signal' => 2]);

        $response = $this->actingAs($admin)->postJson("/api/incidents/{$disaster->id}", [
            'signal' => 9,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['signal']);
        $this->assertSame(2, $disaster->fresh()->signal);
    }

    public function test_update_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $this->actingAs($coordinator)->postJson("/api/incidents/{$disaster->id}", [
            'name' => 'Should not apply',
        ])->assertStatus(403);
    }

    public function test_update_area_persists_ndrrmc_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'population_affected' => 100,
            'severity_level' => 'Medium',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/incidents/{$disaster->id}/areas/{$zone->id}", [
            'population_affected' => 250,
            'severity_level' => 'High',
            'casualties_dead' => 3,
            'casualties_injured' => 10,
            'families_affected' => 60,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.population_affected', 250);
        $response->assertJsonPath('data.severity_level', 'High');
        $response->assertJsonPath('data.casualties_dead', 3);
        $response->assertJsonPath('data.casualties_injured', 10);
        $response->assertJsonPath('data.families_affected', 60);

        $fresh = $zone->fresh();
        $this->assertSame(250, $fresh->population_affected);
        $this->assertSame('High', $fresh->severity_level);
        $this->assertSame(3, $fresh->casualties_dead);
    }

    public function test_update_area_returns_404_when_area_does_not_belong_to_incident(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $otherDisaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $otherDisaster->id]);

        $this->actingAs($admin)->postJson("/api/incidents/{$disaster->id}/areas/{$zone->id}", [
            'population_affected' => 250,
        ])->assertStatus(404);
    }

    public function test_update_area_fails_validation_for_invalid_severity_level(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);

        $this->actingAs($admin)->postJson("/api/incidents/{$disaster->id}/areas/{$zone->id}", [
            'severity_level' => 'Catastrophic',
        ])->assertStatus(422);
    }

    /**
     * IncidentController's $inUseRelations = ['zones', 'requests',
     * 'optimizationRuns'] makes BaseController::destroy() refuse to delete a
     * disaster that still has any of those — the DB-level cascadeOnDelete on
     * app_zones/app_requests is a secondary safety net, not the primary path.
     */
    public function test_destroy_is_blocked_when_the_disaster_still_has_zones_and_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();
        $requestRow = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/incidents/{$disaster->id}");

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'This record is still in use and cannot be deleted.');
        $response->assertJsonPath('errors.in_use.0.label', 'Zones');
        $response->assertJsonPath('errors.in_use.0.count', 1);
        $response->assertJsonPath('errors.in_use.1.label', 'Requests');
        $response->assertJsonPath('errors.in_use.1.count', 1);

        $this->assertDatabaseHas('app_disasters', ['id' => $disaster->id]);
        $this->assertDatabaseHas('app_zones', ['id' => $zone->id]);
        $this->assertDatabaseHas('app_requests', ['id' => $requestRow->id]);
    }

    public function test_destroy_removes_a_disaster_with_no_zones_or_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/incidents/{$disaster->id}");

        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing('app_disasters', ['id' => $disaster->id]);
    }

    public function test_destroy_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $this->actingAs($coordinator)->deleteJson("/api/incidents/{$disaster->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('app_disasters', ['id' => $disaster->id]);
    }

    public function test_destroy_returns_404_for_missing_incident(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->deleteJson('/api/incidents/'.\Illuminate\Support\Str::uuid())
            ->assertStatus(404);
    }

    public function test_destroy_area_removes_the_zone_and_cascades_to_its_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        $resourceType = ResourceType::factory()->create();
        $requestRow = ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/incidents/{$disaster->id}/areas/{$zone->id}");

        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing('app_zones', ['id' => $zone->id]);
        $this->assertDatabaseMissing('app_requests', ['id' => $requestRow->id]);
        // The parent disaster is untouched.
        $this->assertDatabaseHas('app_disasters', ['id' => $disaster->id]);
    }

    public function test_destroy_area_returns_404_when_area_does_not_belong_to_incident(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $otherDisaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $otherDisaster->id]);

        $this->actingAs($admin)->deleteJson("/api/incidents/{$disaster->id}/areas/{$zone->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('app_zones', ['id' => $zone->id]);
    }

    public function test_destroy_area_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);

        $this->actingAs($coordinator)->deleteJson("/api/incidents/{$disaster->id}/areas/{$zone->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('app_zones', ['id' => $zone->id]);
    }
}
