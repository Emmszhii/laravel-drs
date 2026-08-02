<?php

namespace Database\Factories;

use App\Models\Disaster;
use App\Models\OptimizationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptimizationRun>
 */
class OptimizationRunFactory extends Factory
{
    protected $model = OptimizationRun::class;

    public function definition(): array
    {
        return [
            'disaster_id' => Disaster::factory(),
            'triggered_by' => null,
            'algorithm_used' => fake()->randomElement(['pulp_allocation', 'ortools_routing']),
            'objective_value' => fake()->randomFloat(2, 0, 10000),
            'status' => 'completed',
            'response_payload' => [
                'algorithm' => 'pulp_allocation',
                'objective_value' => fake()->randomFloat(2, 0, 10000),
                'note' => 'Fake data seeded for demo/testing purposes.',
            ],
        ];
    }
}
