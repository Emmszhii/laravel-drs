<?php

namespace App\Services\Optimization;

use App\Models\Disaster;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP wrapper around the FastAPI solver-service.
 * Base URL comes from OPTIMIZATION_SERVICE_URL in .env.
 */
class OptimizationServiceClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.optimization.url', env('OPTIMIZATION_SERVICE_URL'));
    }

    /**
     * Calls POST /api/v1/optimize/allocation on the solver-service.
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

        $response = Http::timeout(15)
            ->retry(2, 500)
            ->post("{$this->baseUrl}/api/v1/optimize/allocation", $payload);

        if ($response->failed()) {
            Log::error('Optimization service call failed', [
                'disaster_id' => $disaster->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['algorithm' => 'none', 'objective_value' => 0, 'allocations' => []];
        }

        return $response->json();
    }
}
