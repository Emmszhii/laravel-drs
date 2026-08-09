<?php

namespace Database\Factories;

use App\Models\Disaster;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['After-action summary', 'Resource utilization', 'Response timeline']);

        return [
            'disaster_id' => Disaster::factory(),
            'name' => $type,
            'type' => $type,
            'generated_at' => now(),
        ];
    }
}
