<?php

namespace App\Services\Optimization;

use App\Models\Depot;
use App\Models\Disaster;
use App\Services\Gis\GeofenceService;

/**
 * Pure-PHP fallback allocator, used only when solver-service is unreachable
 * (see OptimizationServiceClient::optimizeAllocation). PuLP/LP remains the
 * default, more rigorous solver — this exists purely so an optimization run
 * still completes with a usable suggestion during a solver-service outage.
 *
 * Nearest-depot-first greedy: zones with the largest unmet demand are
 * served first, each pulling from whichever depot is geographically
 * closest until that depot's stock is exhausted, then the next-nearest.
 *
 * Output shape matches solver-service's AllocationResponse/AllocationLine
 * (algorithm/objective_value/allocations: [{zone_id, depot_id, quantity}])
 * so RunOptimizationJob and the frontend need no special-casing.
 */
class GreedyAllocationSolver
{
    public function __construct(private readonly GeofenceService $geofence)
    {
    }

    /** @return array{algorithm: string, objective_value: float, allocations: array} */
    public function allocate(Disaster $disaster): array
    {
        $zones = $disaster->zones()
            ->orderByDesc('population_affected')
            ->get(['id', 'lat', 'lng', 'population_affected']);

        $depots = Depot::withSum('resources', 'quantity_available')->get();

        $depotCapacity = $depots->mapWithKeys(fn (Depot $depot) => [
            $depot->id => (int) ($depot->resources_sum_quantity_available ?? 0),
        ]);

        $allocations = [];
        $totalUnmet = 0;

        foreach ($zones as $zone) {
            $remainingDemand = (int) $zone->population_affected;

            $nearestDepots = $depots
                ->map(fn (Depot $depot) => [
                    'depot' => $depot,
                    'distance_km' => $this->geofence->distanceKm($zone->lat, $zone->lng, $depot->lat, $depot->lng),
                ])
                ->sortBy('distance_km')
                ->pluck('depot');

            foreach ($nearestDepots as $depot) {
                if ($remainingDemand <= 0) {
                    break;
                }

                $available = $depotCapacity[$depot->id] ?? 0;
                if ($available <= 0) {
                    continue;
                }

                $quantity = min($remainingDemand, $available);
                $allocations[] = [
                    'zone_id' => $zone->id,
                    'depot_id' => $depot->id,
                    'quantity' => $quantity,
                ];

                $depotCapacity[$depot->id] = $available - $quantity;
                $remainingDemand -= $quantity;
            }

            $totalUnmet += $remainingDemand;
        }

        return [
            'algorithm' => 'greedy_nearest_depot_fallback',
            'objective_value' => (float) $totalUnmet,
            'allocations' => $allocations,
        ];
    }
}
