<?php

namespace Database\Factories;

use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceType>
 */
class ResourceTypeFactory extends Factory
{
    protected $model = ResourceType::class;

    public function definition(): array
    {
        [$name, $unit] = fake()->unique()->randomElement([
            ['Medical kits', 'kits'],
            ['Family food packs', 'packs'],
            ['Drinking water (5L)', 'containers'],
            ['Shelter repair kits (tarpaulins)', 'kits'],
            ['Hygiene kits', 'kits'],
            ['Blankets', 'pieces'],
            ['Rice sacks (25kg)', 'sacks'],
            ['Instant noodles cartons', 'cartons'],
            ['First aid kits', 'kits'],
            ['Portable generators', 'units'],
            ['Sleeping mats', 'pieces'],
            ['Water purification tablets', 'packs'],
        ]);

        return [
            'name' => $name,
            'unit' => $unit,
        ];
    }
}
