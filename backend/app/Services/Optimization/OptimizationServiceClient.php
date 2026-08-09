<?php

namespace App\Services\Optimization;

use App\Models\Disaster;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP wrapper around the FastAPI solver-service.
 * Base URL comes from OPTIMIZATION_SERVICE_URL in .env.
 */
class OptimizationServiceClient
{
    protected string $baseUrl;

    public function __construct(private readonly GreedyAllocationSolver $fallback)
    {
        $this->baseUrl = config('services.optimization.url', env('OPTIMIZATION_SERVICE_URL'));
    }

    /**
     * Calls POST /api/v1/optimize/allocation on the solver-service. Falls
     * back to GreedyAllocationSolver (never throws) when solver-service is
     * unreachable or errors, so an optimization run always completes with
     * a usable suggestion instead of failing the job outright.
     *
     * @return array{algorithm: string, objective_value: float, allocations: array}
     */
    public function optimizeAllocation(Disaster $disaster): array
    {
        $payload = [
            'disaster_id' => $disaster->id,
            'zones' => $disaster->zones->map(fn ($z) => [
                'id' => $z->id,
                'lat' => $z->lat,
                'lng' => $z->lng,
                'demand' => $z->population_affected,
            ]),
            'depots' => \App\Models\Depot::all()->map(fn ($d) => [
                'id' => $d->id,
                'lat' => $d->lat,
                'lng' => $d->lng,
            ]),
        ];

        try {
            // throw: false — a persistent non-2xx keeps the existing $response->failed()
            // branch reachable below, instead of retry() itself throwing RequestException.
            $response = Http::timeout(15)
                ->retry(2, 500, throw: false)
                ->post("{$this->baseUrl}/api/v1/optimize/allocation", $payload);
        } catch (ConnectionException $e) {
            Log::warning('Optimization service unreachable — using greedy fallback', [
                'disaster_id' => $disaster->id,
                'error' => $e->getMessage(),
            ]);

            return $this->fallback->allocate($disaster);
        }

        if ($response->failed()) {
            Log::error('Optimization service call failed — using greedy fallback', [
                'disaster_id' => $disaster->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->fallback->allocate($disaster);
        }

        return $response->json();
    }
}
