<?php

namespace App\Services\Directions;

/**
 * Common contract for anything that can turn two points into a driving
 * route — the free self-hosted OSRM engine, or an admin-configured paid
 * provider (see DirectionsRouter for how providers are selected/switched).
 */
interface DirectionsProvider
{
    /**
     * @return array{
     *     ok: bool,
     *     distance_m?: float,
     *     duration_s?: float,
     *     geometry?: array<int, array{0: float, 1: float}>,
     *     steps?: array<int, array{instruction: string, distance_m: float, duration_s: float, maneuver_type: string, maneuver_modifier: ?string, location: array{0: float, 1: float}}>,
     *     error?: string,
     * }
     */
    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array;
}
