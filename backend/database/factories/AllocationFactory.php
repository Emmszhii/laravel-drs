<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\ResourceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class AllocationFactory extends Factory
{
    protected $model = Allocation::class;

    public function definition(): array
    {
        return [
            'request_id' => ResourceRequest::factory(),
            'depot_id' => Depot::factory(),
            'optimization_run_id' => null,
            'quantity_allocated' => fake()->numberBetween(10, 500),
            'status' => fake()->randomElement(['pending', 'en_route', 'delivered', 'cancelled']),
        ];
    }
}
