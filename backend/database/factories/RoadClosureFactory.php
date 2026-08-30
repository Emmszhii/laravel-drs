<?php

namespace Database\Factories;

use App\Models\RoadClosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoadClosure>
 */
class RoadClosureFactory extends Factory
{
    protected $model = RoadClosure::class;

    public function definition(): array
    {
        return [
            'label' => 'Closure: '.fake()->streetName(),
            'lat' => fake()->latitude(14.0, 14.5),
            'lng' => fake()->longitude(121.0, 121.5),
            'radius_m' => 200,
            'is_active' => true,
        ];
    }
}
