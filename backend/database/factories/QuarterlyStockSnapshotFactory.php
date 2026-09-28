<?php

namespace Database\Factories;

use App\Models\Depot;
use App\Models\QuarterlyStockSnapshot;
use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuarterlyStockSnapshot>
 */
class QuarterlyStockSnapshotFactory extends Factory
{
    protected $model = QuarterlyStockSnapshot::class;

    public function definition(): array
    {
        return [
            'depot_id' => Depot::factory(),
            'resource_type_id' => ResourceType::factory(),
            'year' => now()->year,
            'quarter' => now()->quarter,
            'quantity_available_at_close' => fake()->numberBetween(50, 500),
            'quantity_consumed' => fake()->numberBetween(0, 200),
        ];
    }
}
