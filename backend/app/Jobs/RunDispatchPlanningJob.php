<?php

namespace App\Jobs;

use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Persists a CVRPTW routing result (RunOptimizationJob's second stage,
 * OptimizationServiceClient::optimizeRouting()) as real Dispatch rows — one
 * per delivery stop on a vehicle's route — so the /dispatch board reflects
 * the optimizer's decisions instead of only manually-logged entries.
 *
 * Suggested, not auto-sent: status starts `pending` and `driver_id` is left
 * null (a coordinator assigns a driver afterwards), same lifecycle as a
 * manually-logged dispatch reaches once a driver picks it up.
 */
class RunDispatchPlanningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    /**
     * @param  array{routes: array<int, array{vehicle_id: string, stops: array}>}  $routingResult
     */
    public function __construct(public Disaster $disaster, public array $routingResult)
    {
    }

    public function handle(): void
    {
        $routes = $this->routingResult['routes'] ?? [];
        if ($routes === []) {
            return;
        }

        $zonesById = $this->disaster->zones->keyBy('id');

        foreach ($routes as $route) {
            $deliveryStops = collect($route['stops'] ?? [])
                ->filter(fn ($stop) => ($stop['quantity'] ?? 0) > 0);

            if ($deliveryStops->isEmpty()) {
                continue;
            }

            $vehicle = Vehicle::with('depot')->find($route['vehicle_id'] ?? null);
            if ($vehicle === null || $vehicle->depot === null) {
                continue;
            }

            foreach ($deliveryStops as $stop) {
                $zone = $zonesById->get($stop['location_id'] ?? null);
                if ($zone === null) {
                    continue;
                }

                Dispatch::create([
                    'disaster_id' => $this->disaster->id,
                    'zone_id' => $zone->id,
                    'depot_id' => $vehicle->depot_id,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_label' => $vehicle->label,
                    'status' => 'pending',
                    'origin_lat' => $vehicle->depot->lat,
                    'origin_lng' => $vehicle->depot->lng,
                    'destination_lat' => $zone->lat,
                    'destination_lng' => $zone->lng,
                ]);
            }
        }
    }
}
