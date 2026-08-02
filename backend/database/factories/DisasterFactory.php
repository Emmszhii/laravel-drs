<?php

namespace Database\Factories;

use App\Models\Disaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Disaster>
 */
class DisasterFactory extends Factory
{
    protected $model = Disaster::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['Typhoon', 'Flood', 'Earthquake', 'Fire', 'Landslide']);
        $province = fake()->randomElement(['Albay', 'Marikina', 'Leyte', 'Benguet', 'Misamis Oriental', 'Cebu', 'Davao del Sur']);
        $city = fake()->city();

        return [
            'name' => match ($type) {
                'Typhoon' => 'Typhoon '.fake()->firstName(),
                default => $type.' — '.$city,
            },
            'type' => $type,
            'signal' => fake()->numberBetween(1, 5),
            'zone' => "{$city}, {$province}",
            // Roughly bounded within Philippine territory for map realism.
            'lat' => fake()->latitude(5, 19),
            'lng' => fake()->longitude(117, 127),
            'status' => fake()->randomElement(['ACTIVE', 'MONITORING', 'RESOLVED']),
        ];
    }
}
