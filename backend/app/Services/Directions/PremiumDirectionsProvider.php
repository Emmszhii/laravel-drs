<?php

namespace App\Services\Directions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic client for any Mapbox Directions API v5-shaped endpoint — not
 * hardcoded to Mapbox itself, just its request/response contract, so an
 * admin can point this at Mapbox or any compatible provider from
 * Settings → Routing. Config (base URL, key) is per-request, not baked
 * into the container, since it's admin-editable at runtime.
 */
class PremiumDirectionsProvider implements DirectionsProvider
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
    ) {
    }

    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $coordinates = "{$fromLng},{$fromLat};{$toLng},{$toLat}";
        $url = rtrim($this->baseUrl, '/')."/directions/v5/mapbox/driving-traffic/{$coordinates}";

        $response = Http::timeout(10)->get($url, [
            'geometries' => 'geojson',
            'steps' => 'true',
            'overview' => 'full',
            'alternatives' => 'true',
            'annotations' => 'congestion,duration',
            'access_token' => $this->apiKey,
        ]);

        if ($response->status() === 429) {
            throw new ProviderQuotaExceededException('Premium routing provider reported a rate/quota limit (HTTP 429).');
        }

        if ($response->failed()) {
            $body = $response->json() ?? [];
            $message = strtolower((string) ($body['message'] ?? ''));

            if (str_contains($message, 'quota') || str_contains($message, 'limit')) {
                throw new ProviderQuotaExceededException("Premium routing provider reported a quota/limit error: {$message}");
            }

            Log::error('Premium directions request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'error' => 'premium_provider_unavailable'];
        }

        $body = $response->json();

        if (($body['code'] ?? null) !== 'Ok' || empty($body['routes'])) {
            Log::error('Premium provider returned a non-Ok response', ['body' => $body]);

            return ['ok' => false, 'error' => 'no_route_found'];
        }

        // driving-traffic's `duration` is already traffic-adjusted (unlike
        // the plain `driving` profile) — with `alternatives=true` there can
        // be more than one candidate, so pick whichever has the lowest
        // traffic-adjusted duration rather than always taking routes[0].
        $routes = collect($body['routes'])->map(fn (array $route) => $this->normalizeRoute($route))->all();
        usort($routes, fn (array $a, array $b) => $a['duration_in_traffic_s'] <=> $b['duration_in_traffic_s']);

        $chosen = $routes[0];

        return [
            'ok' => true,
            'distance_m' => $chosen['distance_m'],
            'duration_s' => $chosen['duration_s'],
            'duration_in_traffic_s' => $chosen['duration_in_traffic_s'],
            'geometry' => $chosen['geometry'],
            'congestion' => $chosen['congestion'],
            'steps' => $chosen['steps'],
            'routes' => $routes,
            'route_choice_reason' => count($routes) > 1 ? 'lowest_traffic_duration' : 'only_route',
        ];
    }

    /**
     * @return array{distance_m: float, duration_s: float, duration_in_traffic_s: float, geometry: array, congestion: array<int, string>, steps: array}
     */
    protected function normalizeRoute(array $route): array
    {
        $leg = $route['legs'][0] ?? ['steps' => []];

        return [
            'distance_m' => $route['distance'],
            'duration_s' => $route['duration_typical'] ?? $route['duration'],
            'duration_in_traffic_s' => $route['duration'],
            'geometry' => $route['geometry']['coordinates'],
            'congestion' => $leg['annotation']['congestion'] ?? [],
            'steps' => collect($leg['steps'])->map(fn (array $step) => [
                'instruction' => $step['maneuver']['instruction']
                    ?? OsrmDirectionsProvider::humanize($step['maneuver'], $step['name'] ?? ''),
                'distance_m' => $step['distance'],
                'duration_s' => $step['duration'],
                'maneuver_type' => $step['maneuver']['type'],
                'maneuver_modifier' => $step['maneuver']['modifier'] ?? null,
                'location' => $step['maneuver']['location'],
            ])->all(),
        ];
    }
}
