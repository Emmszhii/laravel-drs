<?php

namespace Database\Factories;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispatch>
 */
class DispatchFactory extends Factory
{
    protected $model = Dispatch::class;

    public function definition(): array
    {
        return [
            'disaster_id' => Disaster::factory(),
            'depot_id' => Depot::factory(),
            'driver_id' => User::factory(),
            'vehicle_label' => 'Truck '.fake()->numberBetween(1, 20),
            'status' => 'pending',
            'origin_lat' => fake()->latitude(5, 19),
            'origin_lng' => fake()->longitude(117, 127),
            'destination_lat' => fake()->latitude(5, 19),
            'destination_lng' => fake()->longitude(117, 127),
        ];
    }
}
