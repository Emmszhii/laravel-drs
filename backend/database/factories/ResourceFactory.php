<?php

namespace Database\Factories;

use App\Models\Depot;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        $available = fake()->numberBetween(50, 5000);

        return [
            'depot_id' => Depot::factory(),
            'resource_type_id' => ResourceType::factory(),
            'quantity_available' => $available,
            'quantity_reserved' => fake()->numberBetween(0, (int) ($available * 0.3)),
            'expiry_date' => fake()->optional(0.4)->dateTimeBetween('now', '+2 years'),
        ];
    }
}
