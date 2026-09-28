<?php

namespace Tests\Unit;

use App\Models\QuarterlyStockSnapshot;
use App\Models\Resource;
use App\Services\ReorderSuggestionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderSuggestionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cold_start_with_no_snapshots_returns_no_suggestion(): void
    {
        $resource = Resource::factory()->create(['quantity_available' => 100, 'quantity_reserved' => 0]);

        $result = app(ReorderSuggestionCalculator::class)->suggest($resource);

        $this->assertSame(0.0, $result['avg_quarterly_consumption']);
        $this->assertSame(0, $result['suggested_reorder']);
        $this->assertNotNull($result['note']);
    }

    public function test_populated_history_computes_expected_reorder_quantity(): void
    {
        $resource = Resource::factory()->create(['quantity_available' => 50, 'quantity_reserved' => 0]);

        // Trailing quarters averaging 100 consumed/quarter.
        QuarterlyStockSnapshot::factory()->create([
            'depot_id' => $resource->depot_id,
            'resource_type_id' => $resource->resource_type_id,
            'year' => 2026, 'quarter' => 1, 'quantity_consumed' => 80,
        ]);
        QuarterlyStockSnapshot::factory()->create([
            'depot_id' => $resource->depot_id,
            'resource_type_id' => $resource->resource_type_id,
            'year' => 2026, 'quarter' => 2, 'quantity_consumed' => 120,
        ]);

        $result = app(ReorderSuggestionCalculator::class)->suggest($resource);

        // avg = 100, safety factor 1.5 => 150 - 50 available = 100
        $this->assertSame(100.0, $result['avg_quarterly_consumption']);
        $this->assertSame(100, $result['suggested_reorder']);
        $this->assertNull($result['note']);
    }

    public function test_suggested_reorder_never_goes_negative_when_stock_already_exceeds_target(): void
    {
        $resource = Resource::factory()->create(['quantity_available' => 1000, 'quantity_reserved' => 0]);

        QuarterlyStockSnapshot::factory()->create([
            'depot_id' => $resource->depot_id,
            'resource_type_id' => $resource->resource_type_id,
            'year' => 2026, 'quarter' => 1, 'quantity_consumed' => 10,
        ]);

        $result = app(ReorderSuggestionCalculator::class)->suggest($resource);

        $this->assertSame(0, $result['suggested_reorder']);
    }

    public function test_only_matching_depot_and_resource_type_snapshots_are_considered(): void
    {
        $resource = Resource::factory()->create(['quantity_available' => 50, 'quantity_reserved' => 0]);

        // Unrelated depot/resource_type — must be ignored.
        QuarterlyStockSnapshot::factory()->create(['quantity_consumed' => 9999]);

        $result = app(ReorderSuggestionCalculator::class)->suggest($resource);

        $this->assertSame(0.0, $result['avg_quarterly_consumption']);
        $this->assertNotNull($result['note']);
    }
}
