<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_dispatches_with_related_data(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $depot = Depot::factory()->create(['name' => 'Main Depot']);
        $disaster = Disaster::factory()->create(['name' => 'Test Flood']);
        $driver = User::factory()->create(['role' => 'respondent', 'first_name' => 'Driver', 'last_name' => 'One']);

        $dispatch = Dispatch::factory()->create([
            'depot_id' => $depot->id,
            'disaster_id' => $disaster->id,
            'driver_id' => $driver->id,
            'vehicle_label' => 'Truck 7',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($coordinator)->getJson('/api/dispatches')->assertOk();

        $response->assertJsonPath('success', true);

        $entry = collect($response->json('data'))->firstWhere('id', $dispatch->id);

        $this->assertNotNull($entry);
        $this->assertSame('Truck 7', $entry['vehicle_label']);
        $this->assertSame('pending', $entry['status']);
        $this->assertSame($depot->id, $entry['depot']['id']);
        $this->assertSame('Main Depot', $entry['depot']['name']);
        $this->assertSame($disaster->id, $entry['disaster']['id']);
        $this->assertSame($driver->id, $entry['driver']['id']);
    }

    public function test_pagination_search_filters_by_exact_status(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        Dispatch::factory()->create(['status' => 'pending']);
        Dispatch::factory()->create(['status' => 'delivered']);

        $response = $this->actingAs($coordinator)
            ->getJson('/api/dispatches/pagination-search?filters[status]=delivered&per_page=10&page=1')
            ->assertOk();

        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        foreach ($data as $row) {
            $this->assertSame('delivered', $row['status']);
        }
    }

    public function test_pagination_search_filters_by_disaster_id(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();
        $matching = Dispatch::factory()->create(['disaster_id' => $disaster->id]);
        Dispatch::factory()->create();

        $response = $this->actingAs($coordinator)
            ->getJson("/api/dispatches/pagination-search?filters[disaster_id]={$disaster->id}&per_page=10&page=1")
            ->assertOk();

        $data = $response->json('data.data');
        $ids = collect($data)->pluck('id')->all();
        $this->assertContains($matching->id, $ids);
        foreach ($data as $row) {
            $this->assertSame($disaster->id, $row['disaster_id']);
        }

        $meta = $response->json('data.meta');
        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('total', $meta);
    }

    /**
     * DispatchController::drivers() only filters by is_active — it does not
     * restrict to a "driver" role, so any active user (regardless of role)
     * is returned, and inactive users of any role are excluded.
     */
    public function test_drivers_returns_active_users_with_minimal_fields_and_excludes_inactive(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $activeRespondent = User::factory()->create([
            'role' => 'respondent',
            'first_name' => 'Active',
            'last_name' => 'Driver',
            'is_active' => true,
        ]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($coordinator)->getJson('/api/dispatches/drivers')->assertOk();

        $response->assertJsonPath('success', true);

        $data = collect($response->json('data'));
        $entry = $data->firstWhere('id', $activeRespondent->id);

        $this->assertNotNull($entry);
        $this->assertSame(['id', 'name'], array_keys($entry));
        $this->assertSame('Active Driver', $entry['name']);

        $this->assertNull($data->firstWhere('id', $inactiveUser->id));
    }

    public function test_update_status_persists_new_status_and_returns_updated_dispatch(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $dispatch = Dispatch::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($coordinator)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'delivered'])
            ->assertOk();

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'delivered');
        $response->assertJsonPath('data.id', $dispatch->id);

        $fresh = $dispatch->fresh();
        $this->assertSame('delivered', $fresh->status);
        $this->assertNotNull($fresh->delivered_at);
    }

    public function test_update_status_does_not_set_delivered_at_for_cancelled(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $dispatch = Dispatch::factory()->create(['status' => 'pending']);

        $this->actingAs($coordinator)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'cancelled'])
            ->assertOk();

        $fresh = $dispatch->fresh();
        $this->assertSame('cancelled', $fresh->status);
        $this->assertNull($fresh->delivered_at);
    }

    public function test_update_status_rejects_an_invalid_status_value(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $dispatch = Dispatch::factory()->create(['status' => 'pending']);

        $this->actingAs($coordinator)
            ->postJson("/api/dispatches/{$dispatch->id}/status", ['status' => 'not-a-real-status'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('pending', $dispatch->fresh()->status);
    }

    public function test_update_position_fails_validation_when_lat_and_lng_are_missing(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $dispatch = Dispatch::factory()->create();

        $this->actingAs($coordinator)
            ->postJson("/api/dispatches/{$dispatch->id}/position", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lat', 'lng']);
    }
}
