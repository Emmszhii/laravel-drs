<?php

namespace Database\Factories;

use App\Models\Depot;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'label' => 'Vehicle '.fake()->numberBetween(1, 20),
            'capacity' => fake()->numberBetween(50, 500),
            'depot_id' => Depot::factory(),
            'status' => 'available',
        ];
    }
}
