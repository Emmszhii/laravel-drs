<?php

namespace Database\Factories;

use App\Models\Disaster;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'disaster_id' => Disaster::factory(),
            'name' => 'Brgy. '.fake()->lastName(),
            'population_affected' => fake()->numberBetween(200, 8000),
            'severity_level' => fake()->randomElement(['High', 'Medium']),
            'lat' => fake()->latitude(5, 19),
            'lng' => fake()->longitude(117, 127),
        ];
    }
}
