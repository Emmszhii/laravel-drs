<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

/**
 * Demo item catalog and depots for CDRRMO San Pablo City. ReplenishmentDemoSeeder
 * looks the consumable items up by name. "Handheld radios" is borrowable, so the
 * borrowed-item lifecycle can be demoed. Safe to re-run.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Medical kits', 'unit' => 'kits'],
            ['name' => 'Family food packs', 'unit' => 'packs'],
            ['name' => 'Drinking water (5L)', 'unit' => 'containers'],
            ['name' => 'Shelter repair kits (tarpaulins)', 'unit' => 'kits'],
            ['name' => 'Hygiene kits', 'unit' => 'kits'],
            ['name' => 'Blankets', 'unit' => 'pieces'],
            ['name' => 'Handheld radios', 'unit' => 'units', 'is_consumable' => false],
        ] as $type) {
            ResourceType::firstOrCreate(['name' => $type['name']], $type);
        }

        foreach ([
            ['name' => 'CDRRMO Main Storage', 'address' => 'San Pablo City, Laguna'],
            ['name' => 'CDRRMO Equipment Room', 'address' => 'San Pablo City, Laguna'],
        ] as $depot) {
            Depot::firstOrCreate(['name' => $depot['name']], $depot + ['status' => 'OPERATIONAL']);
        }
    }
}
