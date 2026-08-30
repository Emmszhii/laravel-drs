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
     *     duration_in_traffic_s?: float,
     *     geometry?: array<int, array{0: float, 1: float}>,
     *     congestion?: array<int, string>,
     *     steps?: array<int, array{instruction: string, distance_m: float, duration_s: float, maneuver_type: string, maneuver_modifier: ?string, location: array{0: float, 1: float}}>,
     *     routes?: array<int, array{distance_m: float, duration_s: float, duration_in_traffic_s: float, geometry: array, congestion: array<int, string>, steps: array}>,
     *     route_choice_reason?: string,
     *     error?: string,
     * }
     *
     * `duration_in_traffic_s`, `congestion`, `routes`, and `route_choice_reason`
     * are only populated by traffic-aware providers (see
     * PremiumDirectionsProvider) — OSRM has no live traffic data, so
     * OsrmDirectionsProvider never sets them.
     */
    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array;
}
