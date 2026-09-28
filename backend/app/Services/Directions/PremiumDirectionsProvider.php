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
        $url = rtrim($this->baseUrl, '/')."/directions/v5/mapbox/driving/{$coordinates}";

        $response = Http::timeout(10)->get($url, [
            'geometries' => 'geojson',
            'overview' => 'full',
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

        $route = $body['routes'][0];

        return [
            'ok' => true,
            'distance_m' => $route['distance'],
            'duration_s' => $route['duration'],
            'geometry' => $route['geometry']['coordinates'],
        ];
    }
}
