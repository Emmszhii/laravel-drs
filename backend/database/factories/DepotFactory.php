<?php

namespace Database\Factories;

use App\Models\Depot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Depot>
 */
class DepotFactory extends Factory
{
    protected $model = Depot::class;

    public function definition(): array
    {
        return [
            'name' => 'Depot — '.fake()->city().' Logistics Hub',
            'address' => fake()->streetAddress().', '.fake()->city(),
            'status' => fake()->randomElement(['OPERATIONAL', 'LIMITED CAPACITY', 'OFFLINE']),
            'contact_first_name' => fake()->firstName(),
            'contact_last_name' => fake()->lastName(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => '+63 9'.fake()->numerify('## ### ####'),
        ];
    }
}
