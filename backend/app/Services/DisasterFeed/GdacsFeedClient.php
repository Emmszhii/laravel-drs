<?php

namespace App\Services\DisasterFeed;

use App\Models\DisasterFeedSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Free, keyless multi-hazard feed from GDACS (Global Disaster Alert and
 * Coordination System — UN/EU-backed), filtered to the Philippines.
 * GDACS's JSON shape is less contractually stable than USGS's, so every
 * field is read defensively via data_get() and malformed features are
 * skipped (logged) rather than failing the whole run.
 */
class GdacsFeedClient
{
    /** GDACS event type code => this app's Disaster::type. Unmapped types are skipped. */
    private const TYPE_MAP = [
        'TC' => 'Typhoon',
        'FL' => 'Flood',
        'EQ' => 'Earthquake',
        'WF' => 'Fire',
        'VO' => 'Volcano',
        'DR' => 'Drought',
    ];

    private const ALERT_TO_SIGNAL = [
        'Green' => 2,
        'Orange' => 3,
        'Red' => 4,
    ];

    public function key(): string
    {
        return 'gdacs';
    }

    /**
     * @return array{ok: bool, error: ?string, incidents: array}
     */
    public function fetchRecent(DisasterFeedSetting $settings): array
    {
        if (! $settings->gdacs_enabled) {
            return ['ok' => true, 'error' => null, 'incidents' => []];
        }

        $baseUrl = config('services.disaster_feed.gdacs_url');
        $lookbackDays = config('services.disaster_feed.lookback_days', 3);
        $alertLevels = $this->alertLevelsAtOrAbove($settings->gdacs_min_alert_level);

        try {
            $response = Http::timeout(15)
                ->retry(2, 500, throw: false)
                ->withHeaders(['User-Agent' => config('app.name').' (disaster-feed)'])
                ->get($baseUrl, [
                    'country' => 'Philippines',
                    'fromdate' => now()->subDays($lookbackDays)->toDateString(),
                    'todate' => now()->toDateString(),
                    'alertlevel' => implode(';', $alertLevels),
                    'eventlist' => implode(';', array_keys(self::TYPE_MAP)),
                ]);
        } catch (ConnectionException $e) {
            Log::error('GDACS feed connection failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'gdacs_unavailable', 'incidents' => []];
        }

        if ($response->failed()) {
            Log::error('GDACS feed request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'error' => 'gdacs_unavailable', 'incidents' => []];
        }

        $body = $response->json();
        $features = data_get($body, 'features', []);

        if (! is_array($features)) {
            Log::warning('GDACS feed returned an unexpected shape', ['body' => $body]);

            return ['ok' => false, 'error' => 'gdacs_unexpected_shape', 'incidents' => []];
        }

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
        $eventType = data_get($feature, 'properties.eventtype');
        $eventId = data_get($feature, 'properties.eventid');
        $type = self::TYPE_MAP[$eventType] ?? null;

        if ($type === null || $eventId === null) {
            Log::warning('GDACS feature skipped — unmapped type or missing event id', ['feature' => $feature]);

            return null;
        }

        $lat = data_get($feature, 'geometry.coordinates.1') ?? data_get($feature, 'properties.latitude');
        $lng = data_get($feature, 'geometry.coordinates.0') ?? data_get($feature, 'properties.longitude');

        if ($lat === null || $lng === null) {
            Log::warning('GDACS feature skipped — missing coordinates', ['feature' => $feature]);

            return null;
        }

        $country = data_get($feature, 'properties.country');
        $eventName = data_get($feature, 'properties.eventname');
        $alertLevel = data_get($feature, 'properties.alertlevel', 'Green');
        $zone = $country ?? $eventName ?? sprintf('%.3f, %.3f', $lat, $lng);
        $name = trim(sprintf('%s (%s — %s alert)', $eventName ?? $type, $type, $alertLevel));
        $updated = data_get($feature, 'properties.todate');

        return [
            'source' => $this->key(),
            'external_id' => "{$eventType}-{$eventId}",
            'name' => $name,
            'type' => $type,
            'signal' => $this->signalFor($type, $feature, $alertLevel),
            'zone' => $zone,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'source_url' => data_get($feature, 'properties.url.report'),
            'external_updated_at' => $updated ? Carbon::parse($updated) : null,
        ];
    }

    private function signalFor(string $type, array $feature, string $alertLevel): int
    {
        if ($type === 'Typhoon') {
            $windKmh = data_get($feature, 'properties.severitydata.severity');
            if (is_numeric($windKmh)) {
                return match (true) {
                    $windKmh >= 185 => 5,
                    $windKmh >= 118 => 4,
                    $windKmh >= 89 => 3,
                    $windKmh >= 62 => 2,
                    default => 1,
                };
            }
        }

        return self::ALERT_TO_SIGNAL[$alertLevel] ?? 2;
    }

    /**
     * GDACS alert levels ordered low to high — a "minimum" of Orange means
     * fetch Orange and Red, but not Green.
     */
    private function alertLevelsAtOrAbove(string $minLevel): array
    {
        $order = ['Green', 'Orange', 'Red'];
        $index = array_search($minLevel, $order, true);

        return $index === false ? $order : array_slice($order, $index);
    }
}
