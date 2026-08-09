<?php

namespace Database\Factories;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvacuationCenter>
 */
class EvacuationCenterFactory extends Factory
{
    protected $model = EvacuationCenter::class;

    public function definition(): array
    {
        return [
            'disaster_id' => Disaster::factory(),
            'name' => fake()->company().' Evacuation Center',
            'address' => fake()->streetAddress().', '.fake()->city(),
            'lat' => fake()->latitude(5, 19),
            'lng' => fake()->longitude(117, 127),
            'capacity' => fake()->numberBetween(100, 1000),
            'families_served' => fake()->numberBetween(0, 200),
            'individuals_served' => fake()->numberBetween(0, 800),
            'status' => 'OPEN',
        ];
    }
}
