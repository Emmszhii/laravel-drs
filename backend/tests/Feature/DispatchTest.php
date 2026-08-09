<?php

namespace Tests\Feature;

use App\Events\DispatchPositionUpdated;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_create_a_dispatch(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();
        $depot = Depot::factory()->create();
        $driver = User::factory()->create(['role' => 'respondent']);

        $this->actingAs($coordinator)->postJson('/api/dispatches', [
            'disaster_id' => $disaster->id,
            'depot_id' => $depot->id,
            'driver_id' => $driver->id,
            'vehicle_label' => 'Truck 1',
            'origin_lat' => $depot->lat,
            'origin_lng' => $depot->lng,
            'destination_lat' => $disaster->lat,
            'destination_lng' => $disaster->lng,
        ])->assertStatus(201);

        $this->assertDatabaseHas('app_dispatches', [
            'disaster_id' => $disaster->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
        ]);
    }

    public function test_viewer_cannot_create_a_dispatch(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $disaster = Disaster::factory()->create();
        $depot = Depot::factory()->create();
        $driver = User::factory()->create();

        $this->actingAs($viewer)->postJson('/api/dispatches', [
            'disaster_id' => $disaster->id,
            'depot_id' => $depot->id,
            'driver_id' => $driver->id,
            'origin_lat' => $depot->lat,
            'origin_lng' => $depot->lng,
            'destination_lat' => $disaster->lat,
            'destination_lng' => $disaster->lng,
        ])->assertStatus(403);
    }

    public function test_assigned_driver_can_update_their_own_position(): void
    {
        Event::fake([DispatchPositionUpdated::class]);

        $driver = User::factory()->create(['role' => 'respondent']);
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id, 'status' => 'pending']);

        $this->actingAs($driver)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.5,
            'lng' => 123.5,
        ])->assertOk();

        $dispatch->refresh();
        $this->assertSame(13.5, $dispatch->current_lat);
        $this->assertSame(123.5, $dispatch->current_lng);
        $this->assertSame('en_route', $dispatch->status);
        $this->assertNotNull($dispatch->dispatched_at);

        Event::assertDispatched(DispatchPositionUpdated::class, fn ($event) => $event->dispatch->id === $dispatch->id);
    }

    public function test_a_different_respondent_cannot_update_someone_elses_dispatch_position(): void
    {
        $driver = User::factory()->create(['role' => 'respondent']);
        $otherRespondent = User::factory()->create(['role' => 'respondent']);
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id]);

        $this->actingAs($otherRespondent)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.5,
            'lng' => 123.5,
        ])->assertStatus(403);
    }

    public function test_coordinator_can_update_position_on_behalf_of_a_driver(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $driver = User::factory()->create(['role' => 'respondent']);
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id]);

        $this->actingAs($coordinator)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.5,
            'lng' => 123.5,
        ])->assertOk();
    }
}
