<?php

namespace App\Services\Directions;

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

        $response = Http::timeout(10)
            ->retry(2, 300)
            ->get("{$this->baseUrl}/route/v1/driving/{$coordinates}", [
                'steps' => 'true',
                'geometries' => 'geojson',
                'overview' => 'full',
            ]);

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
        $leg = $route['legs'][0] ?? ['steps' => []];

        return [
            'ok' => true,
            'distance_m' => $route['distance'],
            'duration_s' => $route['duration'],
            'geometry' => $route['geometry']['coordinates'],
            'steps' => collect($leg['steps'])->map(fn (array $step) => [
                'instruction' => self::humanize($step['maneuver'], $step['name'] ?? ''),
                'distance_m' => $step['distance'],
                'duration_s' => $step['duration'],
                'maneuver_type' => $step['maneuver']['type'],
                'maneuver_modifier' => $step['maneuver']['modifier'] ?? null,
                'location' => $step['maneuver']['location'],
            ])->all(),
        ];
    }

    /**
     * Builds a plain-English instruction from OSRM's maneuver type/modifier —
     * OSRM itself only returns structured maneuver data, not a sentence.
     */
    public static function humanize(array $maneuver, string $streetName): string
    {
        $type = $maneuver['type'];
        $modifier = $maneuver['modifier'] ?? null;
        $onto = $streetName !== '' ? " onto {$streetName}" : '';

        return match ($type) {
            'depart' => $streetName !== '' ? "Head out on {$streetName}" : 'Head out',
            'arrive' => 'Arrive at your destination',
            'turn' => match ($modifier) {
                'left', 'sharp left', 'slight left' => "Turn left{$onto}",
                'right', 'sharp right', 'slight right' => "Turn right{$onto}",
                'uturn' => 'Make a U-turn',
                default => "Continue{$onto}",
            },
            'new name' => $streetName !== '' ? "Continue onto {$streetName}" : 'Continue straight',
            'merge' => "Merge{$onto}",
            'on ramp' => "Take the ramp{$onto}",
            'off ramp' => "Take the exit{$onto}",
            'fork' => match ($modifier) {
                'left', 'slight left' => "Keep left{$onto}",
                'right', 'slight right' => "Keep right{$onto}",
                default => "Continue{$onto}",
            },
            'end of road' => match ($modifier) {
                'left' => "Turn left{$onto}",
                'right' => "Turn right{$onto}",
                default => "Continue{$onto}",
            },
            'roundabout', 'rotary', 'roundabout turn' => "Enter the roundabout{$onto}",
            'exit roundabout', 'exit rotary' => "Exit the roundabout{$onto}",
            'continue' => "Continue{$onto}",
            default => $streetName !== '' ? "Continue on {$streetName}" : 'Continue',
        };
    }
}
