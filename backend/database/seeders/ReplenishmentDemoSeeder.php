<?php

namespace Database\Seeders;

use App\Models\HistoryLine;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

/**
 * Mock history standing in for the office's own records until they are encoded: twelve quarters,
 * July 2023 – June 2026 (the window asked of CDRRMO), for every consumable on its real ICS 213-RR
 * form, with unit costs and VED classes. No public dataset holds a CDRRMO's item-level requests,
 * so the figures are built, but on real anchors:
 *
 * - Events are the ones that hit Laguna or San Pablo in that window, in their real quarters:
 *   Egay + habagat (Jul 2023), Carina + habagat (Jul 2024), Kristine (Oct 2024; 30,932 evacuated
 *   in Calabarzon), Crising + habagat (Jul 2025; about 155,000 displaced in Laguna), and the Tino
 *   and Uwan floods in San Pablo (Nov 2025). Their size sets how far each item's use rises.
 * - A family food pack costs ₱797.80, DSWD's awarded price (Aug 2026). Other prices are approximate
 *   2025–2026 Philippine retail prices; replace them with the office's purchase requests.
 * - Routine use is spread over three monthly request lines; an event quarter adds one line tagged
 *   with the event, so DataChecks reads the spike as explained. Kristine's relief lines were released
 *   at 85% of the request, the stock-out the office described, so that quarter's use reads understated.
 *
 * Deterministic (no randomness) and safe to re-run: it skips items that already have history.
 */
class ReplenishmentDemoSeeder extends Seeder
{
    /** Quarter 0 is 2023 Q3. [event tag, date, intensity 0–1] */
    private const EVENTS = [
        0 => ['Egay', '2023-07-27', 0.5],
        4 => ['Carina', '2024-07-24', 0.7],
        5 => ['Kristine', '2024-10-24', 1.0],
        8 => ['Crising', '2025-07-21', 0.8],
        9 => ['Tino-Uwan', '2025-11-10', 0.7],
    ];

    /** Typhoon season runs high; the dry first quarter runs low. */
    private const SEASON = [1 => 0.9, 2 => 1.0, 3 => 1.1, 4 => 1.0];

    public function run(): void
    {
        // name => [unit cost ₱, VED, routine use per quarter, event rise at full intensity, purpose, division]
        $items = [
            // Relief: small all year (fires, small floods), several times larger in an event.
            'Family food packs' => [797.80, 'E', 60, 9, 'relief', 'Operations and Warning'],
            'Hygiene kits' => [650, 'E', 30, 8, 'relief', 'Operations and Warning'],
            'Drinking water (5L)' => [70, 'E', 80, 8, 'relief', 'Operations and Warning'],
            'Blankets' => [280, 'D', 15, 8, 'relief', 'Operations and Warning'],
            'Shelter repair kits (tarpaulins)' => [950, 'D', 3, 12, 'relief', 'Operations and Warning'],
            'Baby wipes' => [75, 'D', 12, 4, 'relief', 'Operations and Warning'],

            // Medical: ERT trainings, standby duty and responses; an event about doubles it.
            'Medical kits' => [1850, 'V', 8, 2, 'team_kit', 'Operations and Warning'],
            'Gloves (L)' => [380, 'V', 6, 1, 'team_kit', 'Operations and Warning'],
            'Face mask' => [140, 'E', 5, 1.5, 'team_kit', 'Operations and Warning'],
            'Cotton balls' => [25, 'E', 12, 1, 'team_kit', 'Operations and Warning'],
            'Gauze 4x4' => [260, 'V', 4, 1, 'team_kit', 'Operations and Warning'],
            'Triangular bandage' => [35, 'E', 15, 1, 'team_kit', 'Operations and Warning'],
            'SAM splint' => [1450, 'V', 2, 1, 'team_kit', 'Operations and Warning'],
            'Lap sponge 8x12' => [18, 'E', 20, 1, 'team_kit', 'Operations and Warning'],
            'Lap sponge 12x12' => [25, 'E', 15, 1, 'team_kit', 'Operations and Warning'],
            'Chest seal' => [1200, 'V', 2, 1, 'team_kit', 'Operations and Warning'],
            'Nasal cannula' => [55, 'V', 4, 1, 'team_kit', 'Operations and Warning'],
            'Elastic bandage 6x3' => [95, 'E', 6, 1, 'team_kit', 'Operations and Warning'],
            'Elastic bandage 4x3' => [70, 'E', 8, 1, 'team_kit', 'Operations and Warning'],
            'Elastic bandage 2x3' => [45, 'E', 6, 1, 'team_kit', 'Operations and Warning'],
            'Micropore tape 1"' => [65, 'E', 8, 1, 'team_kit', 'Operations and Warning'],
            'Micropore tape 2"' => [120, 'E', 6, 1, 'team_kit', 'Operations and Warning'],
            'Transpore tape 1"' => [75, 'E', 6, 1, 'team_kit', 'Operations and Warning'],
            'Transpore tape 2"' => [140, 'E', 4, 1, 'team_kit', 'Operations and Warning'],
            'Leukoplast 2"' => [95, 'E', 4, 1, 'team_kit', 'Operations and Warning'],
            'Leukoplast 3"' => [130, 'E', 3, 1, 'team_kit', 'Operations and Warning'],
            'Hot and cold pack' => [120, 'D', 10, 1, 'team_kit', 'Operations and Warning'],
            'Thermal blanket' => [85, 'V', 10, 2, 'team_kit', 'Operations and Warning'],
            'Band-aid' => [180, 'D', 3, 1, 'team_kit', 'Operations and Warning'],
            'C-collar' => [450, 'V', 2, 1, 'team_kit', 'Operations and Warning'],
            'White flower' => [85, 'D', 10, 1, 'team_kit', 'Operations and Warning'],
            'Alcohol refill' => [150, 'E', 12, 1, 'team_kit', 'Operations and Warning'],
            'Omega pain reliever' => [160, 'D', 4, 1, 'team_kit', 'Operations and Warning'],

            // Office and general: steady, a little more during operations.
            'AAA batteries' => [25, 'E', 24, 1.5, 'team_kit', 'Logistics'],
            'Clipboard' => [65, 'D', 2, 0.5, 'office_use', 'Administration and Training'],
            'Black ballpen' => [12, 'D', 30, 0.5, 'office_use', 'Administration and Training'],
            'Ziplock' => [6, 'D', 40, 0.5, 'office_use', 'Logistics'],
        ];

        foreach ($items as $name => [$cost, $ved, $base, $rise, $purpose, $division]) {
            $type = ResourceType::where('name', $name)->first();
            if (! $type || HistoryLine::where('resource_type_id', $type->id)->exists()) {
                continue;
            }

            $type->update(['unit_cost' => $cost, 'ved_class' => $ved]);

            for ($i = 0; $i < 12; $i++) {
                $year = 2023 + intdiv($i + 2, 4);
                $quarter = ($i + 2) % 4 + 1;
                // ±8% that differs by item and quarter but is the same on every run.
                $wobble = 1 + (crc32("{$name}|{$i}") % 17 - 8) / 100;
                $routine = (int) round($base * self::SEASON[$quarter] * $wobble);

                // Three monthly lines; the last takes the remainder.
                foreach ([0, 1, 2] as $m) {
                    $qty = $m < 2 ? intdiv($routine, 3) : $routine - 2 * intdiv($routine, 3);
                    if ($qty > 0) {
                        $this->line($type, sprintf('%d-%02d-10', $year, ($quarter - 1) * 3 + $m + 1), $qty, $qty, $purpose, null, $division);
                    }
                }

                if ($event = self::EVENTS[$i] ?? null) {
                    [$tag, $date, $intensity] = $event;
                    $qty = (int) round($base * $rise * $intensity * $wobble);
                    $released = $tag === 'Kristine' && $purpose === 'relief' ? (int) round($qty * 0.85) : $qty;
                    if ($qty > 0) {
                        $this->line($type, $date, $qty, $released, $purpose, $tag, $division);
                    }
                }
            }
        }
    }

    private function line(ResourceType $type, string $date, int $requested, int $released, string $purpose, ?string $tag, string $division): void
    {
        HistoryLine::create([
            'date' => $date,
            'resource_type_id' => $type->id,
            'quantity_requested' => $requested,
            'quantity_released' => $released,
            'purpose' => $purpose,
            'event_tag' => $tag,
            'requesting_division' => $division,
        ]);
    }
}
