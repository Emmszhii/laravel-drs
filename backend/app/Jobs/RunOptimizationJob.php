<?php

namespace App\Jobs;

use App\Events\DisasterAlertEvent;
use App\Models\Disaster;
use App\Services\Optimization\OptimizationServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when an incident is created or escalates. Calls the Python
 * solver-service, stores the resulting allocation suggestion, and
 * broadcasts the update so the dashboard refreshes in realtime.
 */
class RunOptimizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(public Disaster $disaster)
    {
    }

    public function handle(OptimizationServiceClient $client): void
    {
        $result = $client->optimizeAllocation($this->disaster);

        // Routing (CVRPTW) is a second stage over the allocation stage's
        // depot->zone quantities — only worth attempting when there's
        // something to route.
        $allocations = $result['allocations'] ?? [];
        $routing = $allocations !== []
            ? $client->optimizeRouting($this->disaster, $allocations)
            : ['algorithm' => 'skipped', 'objective_value' => 0, 'routes' => []];

        // Persist as a suggested (not yet approved) allocation batch.
        // See database/migrations for the `app_optimization_runs` / `app_allocations` tables.
        $this->disaster->optimizationRuns()->create([
            'algorithm_used' => $result['algorithm'] ?? 'unknown',
            'objective_value' => $result['objective_value'] ?? null,
            'status' => 'completed',
            'response_payload' => [...$result, 'routing' => $routing],
        ]);

        if (($routing['routes'] ?? []) !== []) {
            RunDispatchPlanningJob::dispatch($this->disaster, $routing);
        }

        broadcast(new DisasterAlertEvent($this->disaster));
    }
}
