<?php

namespace App\Services\DisasterFeed;

use App\Models\DisasterFeedSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Free, keyless earthquake feed from the USGS Earthquake Hazards Program,
 * filtered to a Philippines bounding box (config('services.disaster_feed.bbox')).
 */
class UsgsEarthquakeFeedClient
{
    public function key(): string
    {
        return 'usgs';
    }

    /**
     * @return array{ok: bool, error: ?string, incidents: array}
     */
    public function fetchRecent(DisasterFeedSetting $settings): array
    {
        if (! $settings->usgs_enabled) {
            return ['ok' => true, 'error' => null, 'incidents' => []];
        }

        $baseUrl = config('services.disaster_feed.usgs_url');
        $bbox = config('services.disaster_feed.bbox');
        $lookbackDays = config('services.disaster_feed.lookback_days', 3);

        try {
            $response = Http::timeout(15)
                ->retry(2, 500)
                ->withHeaders(['User-Agent' => config('app.name').' (disaster-feed)'])
                ->get($baseUrl, [
                    'format' => 'geojson',
                    'starttime' => now()->subDays($lookbackDays)->toIso8601String(),
                    'minlatitude' => $bbox['min_lat'],
                    'maxlatitude' => $bbox['max_lat'],
                    'minlongitude' => $bbox['min_lng'],
                    'maxlongitude' => $bbox['max_lng'],
                    'minmagnitude' => $settings->usgs_min_magnitude,
                    'orderby' => 'time',
                    'limit' => 200,
                ]);
        } catch (ConnectionException $e) {
            Log::error('USGS feed connection failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'usgs_unavailable', 'incidents' => []];
        }

        if ($response->failed()) {
            Log::error('USGS feed request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'error' => 'usgs_unavailable', 'incidents' => []];
        }

        $features = $response->json('features') ?? [];
        $incidents = [];

        foreach ($features as $feature) {
            $incident = $this->mapFeature($feature);
            if ($incident !== null) {
                $incidents[] = $incident;
            }
        }

        return ['ok' => true, 'error' => null, 'incidents' => $incidents];
    }

    private function mapFeature(array $feature): ?array
    {
        $properties = $feature['properties'] ?? [];
        $coordinates = $feature['geometry']['coordinates'] ?? null;
        $magnitude = $properties['mag'] ?? null;

        if ($magnitude === null || ! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        // GeoJSON order is [lng, lat, depth] — the reverse of how this app stores coordinates.
        [$lng, $lat] = $coordinates;
        $place = $properties['place'] ?? 'Philippines';

        return [
            'source' => $this->key(),
            'external_id' => $feature['id'],
            'name' => Str::limit(sprintf('M %.1f Earthquake — %s', $magnitude, $place), 250),
            'type' => 'Earthquake',
            'signal' => $this->magnitudeToSignal((float) $magnitude),
            'zone' => $place,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'source_url' => $properties['url'] ?? null,
            'external_updated_at' => isset($properties['updated'])
                ? Carbon::createFromTimestampMs($properties['updated'])
                : null,
        ];
    }

    private function magnitudeToSignal(float $magnitude): int
    {
        return match (true) {
            $magnitude >= 7.0 => 5,
            $magnitude >= 6.0 => 4,
            $magnitude >= 5.0 => 3,
            $magnitude >= 4.0 => 2,
            default => 1,
        };
    }
}
