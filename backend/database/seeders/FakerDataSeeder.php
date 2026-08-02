<?php

namespace Database\Seeders;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\OptimizationRun;
use App\Models\Resource;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use App\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * Bulk synthetic data appended to whatever is already in the database —
 * unlike DisasterDataSeeder (a handful of fixed, real-location records),
 * this adds volume via Faker for load-testing pagination/charts/maps.
 * Safe to re-run: every call adds a fresh batch rather than truncating.
 */
class FakerDataSeeder extends Seeder
{
    public function run(): void
    {
        $resourceTypes = ResourceType::all();
        if ($resourceTypes->isEmpty()) {
            $resourceTypes = ResourceType::factory()->count(8)->create();
        }

        $depots = Depot::factory()
            ->count(5)
            ->create()
            ->each(function (Depot $depot) use ($resourceTypes) {
                $resourceTypes->random(min(4, $resourceTypes->count()))
                    ->each(fn (ResourceType $type) => Resource::factory()->create([
                        'depot_id' => $depot->id,
                        'resource_type_id' => $type->id,
                    ]));
            });

        Disaster::factory()
            ->count(10)
            ->create()
            ->each(function (Disaster $disaster) use ($resourceTypes, $depots) {
                $zones = Zone::factory()
                    ->count(fake()->numberBetween(2, 5))
                    ->create(['disaster_id' => $disaster->id]);

                OptimizationRun::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->create(['disaster_id' => $disaster->id]);

                $zones->each(function (Zone $zone) use ($resourceTypes, $depots) {
                    ResourceRequest::factory()
                        ->count(fake()->numberBetween(1, 4))
                        ->create(['zone_id' => $zone->id, 'resource_type_id' => $resourceTypes->random()->id])
                        ->each(function (ResourceRequest $request) use ($depots) {
                            if (fake()->boolean(70)) {
                                Allocation::factory()->create([
                                    'request_id' => $request->id,
                                    'depot_id' => $depots->random()->id,
                                ]);
                            }
                        });
                });
            });
    }
}
