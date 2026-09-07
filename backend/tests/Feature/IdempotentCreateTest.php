<?php

namespace Tests\Feature;

use App\Models\ResourceType;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The offline-sync outbox (Offline/sync.ts) retries a queued POST after a
 * partial network failure using the same client-generated id — these must
 * never create a duplicate row on retry.
 */
class IdempotentCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_the_same_incident_id_twice_creates_only_one_row(): void
    {
        $coordinator = User::factory()->create(['role' => 'incident_management']);
        $id = (string) Str::uuid();

        $payload = [
            'id' => $id,
            'name' => 'Offline-logged Incident',
            'type' => 'flood',
            'signal' => 2,
            'zone' => 'Test Zone',
            'lat' => 13.1,
            'lng' => 123.7,
        ];

        $this->actingAs($coordinator)->postJson('/api/incidents', $payload)->assertStatus(201);
        $this->actingAs($coordinator)->postJson('/api/incidents', $payload)->assertStatus(201);

        $this->assertSame(1, \App\Models\Disaster::where('id', $id)->count());
    }

    public function test_posting_the_same_area_id_twice_creates_only_one_zone(): void
    {
        $coordinator = User::factory()->create(['role' => 'incident_management']);
        $disaster = \App\Models\Disaster::factory()->create();
        $id = (string) Str::uuid();

        $payload = [
            'id' => $id,
            'name' => 'Brgy. Test',
            'population_affected' => 500,
            'severity_level' => 'High',
        ];

        $this->actingAs($coordinator)->postJson("/api/incidents/{$disaster->id}/areas", $payload)->assertStatus(201);
        $this->actingAs($coordinator)->postJson("/api/incidents/{$disaster->id}/areas", $payload)->assertStatus(201);

        $this->assertSame(1, Zone::where('id', $id)->count());
    }

    public function test_posting_the_same_request_id_twice_creates_only_one_row(): void
    {
        $coordinator = User::factory()->create(['role' => 'incident_management']);
        $zone = Zone::factory()->create();
        $resourceType = ResourceType::factory()->create();
        $id = (string) Str::uuid();

        $payload = [
            'id' => $id,
            'zone_id' => $zone->id,
            'resource_type_id' => $resourceType->id,
            'quantity_requested' => 50,
            'priority' => 'high',
        ];

        $this->actingAs($coordinator)->postJson('/api/requests', $payload)->assertStatus(201);
        $this->actingAs($coordinator)->postJson('/api/requests', $payload)->assertStatus(201);

        $this->assertSame(1, \App\Models\ResourceRequest::where('id', $id)->count());
    }
}
