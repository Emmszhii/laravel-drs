<?php

namespace Database\Seeders;

use App\Models\HistoryLine;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

/**
 * Illustrative unit costs, VED classes and eight quarters of encoded history
 * (Q3 2024 – Q2 2026, including the October 2024 Kristine quarter) so the
 * replenishment dashboard has something to solve. Prices follow the paper's
 * worked example (Table 5); replace with the office's own encoded records.
 * Safe to re-run: it skips items that already have history.
 */
class ReplenishmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        // name => [unit_cost, VED, quarterly use oldest first]
        $demo = [
            'Family food packs' => [500, 'E', [300, 900, 320, 310, 330, 300, 310, 290]],
            'Hygiene kits' => [350, 'E', [200, 420, 210, 190, 200, 205, 195, 200]],
            'Drinking water (5L)' => [150, 'E', [400, 1100, 380, 420, 410, 390, 400, 420]],
            'Medical kits' => [900, 'V', [80, 140, 85, 90, 85, 80, 85, 90]],
            'Blankets' => [250, 'D', [40, 160, 30, 35, 40, 30, 45, 35]],
            'Shelter repair kits (tarpaulins)' => [650, 'D', [10, 90, 5, 8, 6, 10, 7, 9]],
        ];

        foreach ($demo as $name => [$cost, $ved, $uses]) {
            $type = ResourceType::where('name', $name)->first();
            if (! $type || HistoryLine::where('resource_type_id', $type->id)->exists()) {
                continue;
            }

            $type->update(['unit_cost' => $cost, 'ved_class' => $ved]);

            foreach ($uses as $i => $use) {
                $year = 2024 + intdiv($i + 2, 4); // i = 0 is Q3 2024
                $quarter = ($i + 2) % 4 + 1;
                HistoryLine::create([
                    'date' => sprintf('%d-%02d-15', $year, ($quarter - 1) * 3 + 2),
                    'resource_type_id' => $type->id,
                    'quantity_requested' => $use,
                    'quantity_released' => $use,
                    'purpose' => 'relief',
                    'event_tag' => $i === 1 ? 'Kristine' : null,
                    'requesting_division' => 'ERT',
                ]);
            }
        }
    }
}
