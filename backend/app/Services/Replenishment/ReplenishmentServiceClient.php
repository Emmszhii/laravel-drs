<?php

namespace App\Services\Replenishment;

use Illuminate\Support\Facades\Http;

/**
 * Calls the solver-service's POST /api/v1/replenishment/solve. Unlike the
 * old allocation client there is no fallback: a failed solve fails the run,
 * and the Admin re-runs it.
 */
class ReplenishmentServiceClient
{
    public function solve(array $payload): array
    {
        return Http::timeout(60)
            ->post(rtrim(config('services.optimization.url'), '/') . '/api/v1/replenishment/solve', $payload)
            ->throw()
            ->json();
    }
}
