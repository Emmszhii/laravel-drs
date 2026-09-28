<?php

namespace App\Services\Directions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Free, self-hosted routing via OSRM (no API key, no usage cap, no live
 * traffic). Base URL comes from OSRM_URL — see docker/osrm/prepare.sh for
 * how the underlying road-network data is built.
 */
class OsrmDirectionsProvider implements DirectionsProvider
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.osrm.url');
    }

    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        // OSRM expects lng,lat order — the reverse of how this app stores coordinates.
        $coordinates = "{$fromLng},{$fromLat};{$toLng},{$toLat}";

        try {
            $response = Http::timeout(10)
                ->retry(2, 300)
                ->get("{$this->baseUrl}/route/v1/driving/{$coordinates}", [
                    'geometries' => 'geojson',
                    'overview' => 'full',
                ]);
        } catch (ConnectionException $e) {
            Log::error('OSRM connection failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'osrm_unavailable'];
        }

        if ($response->failed()) {
            Log::error('OSRM directions request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'error' => 'osrm_unavailable'];
        }

        $body = $response->json();

        if (($body['code'] ?? null) !== 'Ok' || empty($body['routes'])) {
            Log::error('OSRM returned a non-Ok response', ['body' => $body]);

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
