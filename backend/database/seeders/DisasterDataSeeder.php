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

        Zone::create([
            'disaster_id' => $marina->id, 'name' => 'Brgy. Bogtong', 'population_affected' => 3200,
            'severity_level' => 'High', 'lat' => 13.141, 'lng' => 123.745,
            // Sample NDRRMC/Sendai SitRep figures for demo/evaluation purposes.
            'casualties_dead' => 2, 'casualties_injured' => 14, 'casualties_missing' => 1,
            'families_affected' => 640, 'houses_totally_damaged' => 38, 'houses_partially_damaged' => 112,
            'estimated_damage_cost' => 4500000.00,
        ]);
        Zone::create([
            'disaster_id' => $marina->id, 'name' => 'Brgy. Rawis', 'population_affected' => 5400,
            'severity_level' => 'Medium', 'lat' => 13.150, 'lng' => 123.752,
            'casualties_dead' => 0, 'casualties_injured' => 6, 'casualties_missing' => 0,
            'families_affected' => 980, 'houses_totally_damaged' => 12, 'houses_partially_damaged' => 205,
            'estimated_damage_cost' => 2100000.00,
        ]);

        \App\Models\EvacuationCenter::create([
            'disaster_id' => $marina->id,
            'name' => 'Legazpi City Convention Center',
            'address' => 'Rizal St, Legazpi City, Albay',
            'lat' => 13.1420, 'lng' => 123.7480,
            'capacity' => 800, 'families_served' => 210, 'individuals_served' => 890,
            'status' => 'OPEN',
        ]);

        Disaster::create([
            'name' => 'Flooding — Marikina River',
            'type' => 'Flood',
            'signal' => 3,
            'zone' => 'Marikina City, Metro Manila',
            'lat' => 14.6507,
            'lng' => 121.1029,
            'status' => 'ACTIVE',
        ]);

        // Feed-sourced examples (shape matches App\Services\DisasterFeed\* output)
        // so the Settings → Disaster feed UI and auto-resolve are demoable
        // without waiting on a live poll — see CLAUDE.md's "Automated disaster
        // feed" section.
        Disaster::create([
            'name' => 'M 5.4 Earthquake — 21 km SE of Bacacay',
            'type' => 'Earthquake',
            'signal' => 3,
            'zone' => '21 km SE of Bacacay, Philippines',
            'lat' => 13.2130,
            'lng' => 123.8560,
            'status' => 'ACTIVE',
            'source' => 'usgs',
            'external_id' => 'us7000seed1',
            'source_url' => 'https://earthquake.usgs.gov/earthquakes/eventpage/us7000seed1',
            'external_updated_at' => now()->subHours(6),
        ]);

        // Backdated past DISASTER_FEED_AUTO_RESOLVE_MAX_DAYS with no zones/requests —
        // the intended first subject for AutoResolveIncidentsJob.
        $stale = Disaster::create([
            'name' => 'Tropical Cyclone Bising (GDACS Orange alert)',
            'type' => 'Typhoon',
            'signal' => 2,
            'zone' => 'Philippines',
            'lat' => 12.8797,
            'lng' => 124.7740,
            'status' => 'ACTIVE',
            'source' => 'gdacs',
            'external_id' => 'TC-1000987',
            'source_url' => 'https://www.gdacs.org/report.aspx?eventid=1000987&eventtype=TC',
            'external_updated_at' => now()->subDays(6),
        ]);
        // created_at isn't fillable and saveQuietly() skips the audit observer
        // (no spurious app_loggers row for what is really just seed backdating).
        $stale->forceFill(['created_at' => now()->subDays(6)])->saveQuietly();
    }
}
