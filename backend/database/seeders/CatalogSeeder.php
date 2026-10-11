<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Kit;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

/**
 * Item catalog and depots for CDRRMO San Pablo City. ReplenishmentDemoSeeder looks the
 * demo items up by name. "Handheld radios" is borrowable, so the borrowed-item lifecycle
 * can be demoed. The second list is what the office's own ICS 213-RR form and borrow
 * logbook name (photos of 2026-09-03); this seeder gives no unit costs (ReplenishmentDemoSeeder
 * adds mock ones to the consumables), and the solver skips an item until it has one. Safe to re-run: it creates what is
 * missing and never overwrites what the office has edited.
 *
 * Sizes are separate items, and the unit is the smallest amount the office hands out:
 * "Cotton ball = 1/4 pack" on the form is 1 quarter-pack here, because quantities are whole numbers.
 * Three lines on the photographed form were too faint to read ("Pads refill", "ETD",
 * "Kepeck instant") and are left out; add them on the Items page.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // [name, unit, category, consumable]
        $items = [
            ['Medical kits', 'kits', 'medical', true],
            ['Family food packs', 'packs', 'general', true],
            ['Drinking water (5L)', 'containers', 'general', true],
            ['Shelter repair kits (tarpaulins)', 'kits', 'general', true],
            ['Hygiene kits', 'kits', 'general', true],
            ['Blankets', 'pieces', 'general', true],
            ['Handheld radios', 'units', 'general', false],

            ['Gloves (L)', 'boxes', 'medical', true],
            ['Face mask', 'boxes', 'medical', true],
            ['Cotton balls', 'quarter-packs', 'medical', true],
            ['Gauze 4x4', 'boxes', 'medical', true],
            ['Triangular bandage', 'pieces', 'medical', true],
            ['SAM splint', 'pieces', 'medical', true],
            ['Lap sponge 8x12', 'pieces', 'medical', true],
            ['Lap sponge 12x12', 'pieces', 'medical', true],
            ['Chest seal', 'sets', 'medical', true],
            ['Nasal cannula', 'pieces', 'medical', true],
            ['Elastic bandage 6x3', 'pieces', 'medical', true],
            ['Elastic bandage 4x3', 'pieces', 'medical', true],
            ['Elastic bandage 2x3', 'pieces', 'medical', true],
            ['Micropore tape 1"', 'pieces', 'medical', true],
            ['Micropore tape 2"', 'pieces', 'medical', true],
            ['Transpore tape 1"', 'pieces', 'medical', true],
            ['Transpore tape 2"', 'pieces', 'medical', true],
            ['Leukoplast 2"', 'pieces', 'medical', true],
            ['Leukoplast 3"', 'pieces', 'medical', true],
            ['Hot and cold pack', 'pieces', 'medical', true],
            ['Thermal blanket', 'pieces', 'medical', true],
            ['Band-aid', 'boxes', 'medical', true],
            ['C-collar', 'pieces', 'medical', true],
            ['White flower', 'pieces', 'medical', true],
            ['Alcohol refill', 'liters', 'medical', true],
            ['Omega pain reliever', 'bottles', 'medical', true],
            ['Thermal gun', 'units', 'medical', false],
            ['Blood pressure apparatus', 'sets', 'medical', false],
            ['Pulse oximeter', 'units', 'medical', false],

            ['Clipboard', 'pieces', 'office', true],
            ['Black ballpen', 'pieces', 'office', true],
            ['Ziplock', 'pieces', 'office', true],

            ['AAA batteries', 'pieces', 'general', true],
            ['Baby wipes', 'pieces', 'general', true],
            ['Bolt cutter', 'units', 'general', false], // the borrow logbook's "Tools"
        ];

        foreach ($items as [$name, $unit, $category, $consumable]) {
            $type = ResourceType::firstOrCreate(['name' => $name], ['unit' => $unit, 'category' => $category, 'is_consumable' => $consumable]);
            if ($type->category === 'general' && $category !== 'general') {
                $type->update(['category' => $category]); // an item made before categories existed
            }
        }

        foreach ([
            ['name' => 'CDRRMO Main Storage', 'address' => 'San Pablo City, Laguna'],
            ['name' => 'CDRRMO Equipment Room', 'address' => 'San Pablo City, Laguna'],
        ] as $depot) {
            Depot::firstOrCreate(['name' => $depot['name']], $depot + ['status' => 'OPERATIONAL']);
        }

        $this->backpack();
    }

    /** "#1 backpack" on the form: the standard medical backpack, with the quantities finally written on it. */
    private function backpack(): void
    {
        $kit = Kit::firstOrCreate(['name' => 'Medical backpack #1']);
        if (! $kit->wasRecentlyCreated) {
            return;
        }

        foreach ([
            'Gloves (L)' => 1, 'Face mask' => 1, 'Cotton balls' => 1, 'Gauze 4x4' => 1, 'Triangular bandage' => 6, 'SAM splint' => 1,
            'Lap sponge 8x12' => 6, 'Lap sponge 12x12' => 6, 'Chest seal' => 1, 'Nasal cannula' => 2,
            'Elastic bandage 6x3' => 6, 'Elastic bandage 4x3' => 6, 'Elastic bandage 2x3' => 6,
            'Micropore tape 1"' => 1, 'Micropore tape 2"' => 1, 'Transpore tape 1"' => 1, 'Transpore tape 2"' => 1,
            'Leukoplast 2"' => 1, 'Leukoplast 3"' => 1, 'Hot and cold pack' => 2, 'Thermal blanket' => 2, 'Ziplock' => 10,
            'Band-aid' => 1, 'C-collar' => 1, 'Clipboard' => 1, 'Black ballpen' => 2,
            'Thermal gun' => 1, 'Blood pressure apparatus' => 1, 'Pulse oximeter' => 1,
            'White flower' => 2, 'Alcohol refill' => 1, 'AAA batteries' => 2, 'Baby wipes' => 2, 'Omega pain reliever' => 1,
        ] as $name => $quantity) {
            $kit->lines()->create(['resource_type_id' => ResourceType::where('name', $name)->value('id'), 'quantity' => $quantity]);
        }
    }
}
