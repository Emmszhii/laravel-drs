<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Disaster;
use App\Models\ResourceType;
use App\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * Synthetic-but-realistic disaster data for research evaluation and demos.
 * Locations mirror the interactive prototype: Albay province, Marikina City,
 * Tacloban City, Itogon (Benguet), and Cagayan de Oro.
 */
class DisasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $resourceTypes = collect([
            ['name' => 'Medical kits', 'unit' => 'kits'],
            ['name' => 'Family food packs', 'unit' => 'packs'],
            ['name' => 'Drinking water (5L)', 'unit' => 'containers'],
            ['name' => 'Shelter repair kits (tarpaulins)', 'unit' => 'kits'],
            ['name' => 'Hygiene kits', 'unit' => 'kits'],
            ['name' => 'Blankets', 'unit' => 'pieces'],
        ])->map(fn ($t) => ResourceType::create($t));

        $depots = collect([
            ['name' => 'Depot A — Legazpi City Central Warehouse', 'address' => 'Rizal St, Legazpi City, Albay', 'lat' => 13.1449, 'lng' => 123.7515, 'contact_first_name' => 'Ramon', 'contact_last_name' => 'Bautista', 'contact_email' => 'r.bautista@albaydrrmc.gov.ph', 'contact_phone' => '+63 917 123 4567'],
            ['name' => 'Depot B — Daraga Logistics Hub', 'address' => 'Poblacion, Daraga, Albay', 'lat' => 13.1510, 'lng' => 123.6980, 'contact_first_name' => 'Liza', 'contact_last_name' => 'Formaran', 'contact_email' => 'l.formaran@albaydrrmc.gov.ph', 'contact_phone' => '+63 917 234 5678'],
            ['name' => 'Depot D — NCR Central Hub', 'address' => 'Quezon City, Metro Manila', 'lat' => 14.6760, 'lng' => 121.0437, 'contact_first_name' => 'Carmela', 'contact_last_name' => 'Uy', 'contact_email' => 'c.uy@ndrrmc.gov.ph', 'contact_phone' => '+63 917 456 7890'],
        ])->map(fn ($d) => Depot::create($d + ['status' => 'OPERATIONAL']));

        $marina = Disaster::create([
            'name' => 'Typhoon Marina',
            'type' => 'Typhoon',
            'signal' => 4,
            'zone' => 'Legazpi City, Albay',
            'lat' => 13.1391,
            'lng' => 123.7438,
            'status' => 'ACTIVE',
        ]);

        Zone::create(['disaster_id' => $marina->id, 'name' => 'Brgy. Bogtong', 'population_affected' => 3200, 'severity_level' => 'High', 'lat' => 13.141, 'lng' => 123.745]);
        Zone::create(['disaster_id' => $marina->id, 'name' => 'Brgy. Rawis', 'population_affected' => 5400, 'severity_level' => 'Medium', 'lat' => 13.150, 'lng' => 123.752]);

        Disaster::create([
            'name' => 'Flooding — Marikina River',
            'type' => 'Flood',
            'signal' => 3,
            'zone' => 'Marikina City, Metro Manila',
            'lat' => 14.6507,
            'lng' => 121.1029,
            'status' => 'ACTIVE',
        ]);
    }
}
