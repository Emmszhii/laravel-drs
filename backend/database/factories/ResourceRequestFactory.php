<?php

namespace Database\Factories;

use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceRequest>
 */
class ResourceRequestFactory extends Factory
{
    protected $model = ResourceRequest::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'resource_type_id' => ResourceType::factory(),
            'quantity_requested' => fake()->numberBetween(10, 1000),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['pending', 'enroute', 'unmet', 'fulfilled']),
            'requested_by' => null,
        ];
    }
}
