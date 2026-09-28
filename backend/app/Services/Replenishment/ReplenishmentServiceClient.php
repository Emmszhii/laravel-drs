<?php

namespace App\Services\Replenishment;

use Illuminate\Support\Facades\Http;

/**
 * Calls the solver-service's /api/v1/replenishment endpoints. Unlike the
 * old allocation client there is no fallback: a failed solve fails the run,
 * and the Admin re-runs it.
 */
class ReplenishmentServiceClient
{
    public function solve(array $payload): array
    {
        return $this->post('solve', $payload);
    }

    /** 11-point budget curve (50%-150%) plus the "next P10,000 goes to" diff. */
    public function budgetCurve(array $payload): array
    {
        return $this->post('budget-curve', $payload);
    }

    private function post(string $endpoint, array $payload): array
    {
        return Http::timeout(120)
            ->post(rtrim(config('services.optimization.url'), '/') . "/api/v1/replenishment/{$endpoint}", $payload)
            ->throw()
            ->json();
    }
}
