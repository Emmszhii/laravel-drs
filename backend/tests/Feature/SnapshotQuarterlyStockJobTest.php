<?php

namespace Tests\Feature;

use App\Jobs\SnapshotQuarterlyStockJob;
use App\Models\Allocation;
use App\Models\Depot;
use App\Models\QuarterlyStockSnapshot;
use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnapshotQuarterlyStockJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_snapshots_closing_stock_and_sums_fulfilled_consumption_for_the_elapsed_quarter(): void
    {
        $depot = Depot::factory()->create();
        $resourceType = ResourceType::factory()->create();
        $resource = Resource::factory()->create([
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'quantity_available' => 70,
            'quantity_reserved' => 0,
        ]);

        $fulfilledRequest = ResourceRequest::factory()->create(['resource_type_id' => $resourceType->id]);
        Allocation::factory()->create([
            'request_id' => $fulfilledRequest->id,
            'depot_id' => $depot->id,
            'quantity_allocated' => 30,
            'status' => 'fulfilled',
        ]);

        // Not yet fulfilled — must be excluded from the consumption sum.
        $reservedRequest = ResourceRequest::factory()->create(['resource_type_id' => $resourceType->id]);
        Allocation::factory()->create([
            'request_id' => $reservedRequest->id,
            'depot_id' => $depot->id,
            'quantity_allocated' => 999,
            'status' => 'reserved',
        ]);

        (new SnapshotQuarterlyStockJob)->handle();

        $expectedClosing = now()->subDay();

        $this->assertDatabaseHas('app_quarterly_stock_snapshots', [
            'depot_id' => $depot->id,
            'resource_type_id' => $resourceType->id,
            'year' => $expectedClosing->year,
            'quarter' => $expectedClosing->quarter,
            'quantity_available_at_close' => 70,
            'quantity_consumed' => 30,
        ]);
    }

    public function test_running_it_twice_for_the_same_quarter_updates_rather_than_duplicates(): void
    {
        $resource = Resource::factory()->create(['quantity_available' => 10, 'quantity_reserved' => 0]);

        (new SnapshotQuarterlyStockJob)->handle();
        (new SnapshotQuarterlyStockJob)->handle();

        $this->assertSame(1, QuarterlyStockSnapshot::where('depot_id', $resource->depot_id)
            ->where('resource_type_id', $resource->resource_type_id)
            ->count());
    }
}
