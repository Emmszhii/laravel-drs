<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ResourceLoanController reserves stock on issue and releases it on return
 * (borrowed items are "reserved," not deducted, since they come back).
 */
class ResourceLoanReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_issuing_a_loan_reserves_stock(): void
    {
        $resourceType = ResourceType::factory()->create(['is_consumable' => false]);
        $resource = Resource::factory()->create([
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 20,
            'quantity_reserved' => 0,
        ]);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)->postJson('/api/resource-loans', [
            'resource_id' => $resource->id,
            'borrower_name' => 'Juan Dela Cruz',
            'quantity' => 5,
        ])->assertStatus(201);

        $this->assertDatabaseHas('app_resources', [
            'id' => $resource->id,
            'quantity_available' => 20,
            'quantity_reserved' => 5,
        ]);
        $this->assertDatabaseHas('app_resource_loans', [
            'resource_id' => $resource->id,
            'quantity' => 5,
            'status' => 'issued',
        ]);
    }

    public function test_issuing_a_loan_beyond_available_stock_is_rejected(): void
    {
        $resourceType = ResourceType::factory()->create(['is_consumable' => false]);
        $resource = Resource::factory()->create([
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 10,
            'quantity_reserved' => 8,
        ]);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)->postJson('/api/resource-loans', [
            'resource_id' => $resource->id,
            'borrower_name' => 'Juan Dela Cruz',
            'quantity' => 5,
        ])->assertStatus(422);

        $this->assertDatabaseHas('app_resources', ['id' => $resource->id, 'quantity_reserved' => 8]);
    }

    public function test_issuing_a_loan_for_a_consumable_resource_type_is_rejected(): void
    {
        $resourceType = ResourceType::factory()->create(['is_consumable' => true]);
        $resource = Resource::factory()->create(['resource_type_id' => $resourceType->id]);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)->postJson('/api/resource-loans', [
            'resource_id' => $resource->id,
            'borrower_name' => 'Juan Dela Cruz',
            'quantity' => 1,
        ])->assertStatus(422);
    }

    public function test_returning_a_loan_releases_the_reservation(): void
    {
        $resourceType = ResourceType::factory()->create(['is_consumable' => false]);
        $resource = Resource::factory()->create([
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 20,
            'quantity_reserved' => 5,
        ]);
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)->postJson('/api/resource-loans', [
            'resource_id' => $resource->id,
            'borrower_name' => 'Juan Dela Cruz',
            'quantity' => 5,
        ])->assertStatus(201);

        $loanId = \App\Models\ResourceLoan::first()->id;

        $this->actingAs($logistics)->postJson("/api/resource-loans/{$loanId}", [
            'status' => 'returned',
        ])->assertOk();

        $this->assertDatabaseHas('app_resources', ['id' => $resource->id, 'quantity_reserved' => 5]);
        $this->assertDatabaseHas('app_resource_loans', ['id' => $loanId, 'status' => 'returned']);
        $this->assertNotNull(\App\Models\ResourceLoan::find($loanId)->returned_at);
    }
}
