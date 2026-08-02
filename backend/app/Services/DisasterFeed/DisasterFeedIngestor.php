<?php

namespace App\Services\DisasterFeed;

use App\Events\DisasterAlertEvent;
use App\Models\Disaster;
use App\Services\Gis\GeofenceService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Dedups and upserts normalized incidents (from UsgsEarthquakeFeedClient /
 * GdacsFeedClient) into app_disasters. Kept separate from the polling job
 * so it's unit-testable without the queue.
 */
class DisasterFeedIngestor
{
    /** A manually-logged incident within this radius of a feed incident of the same type is treated as the same event. */
    private const NEAR_DUPLICATE_RADIUS_KM = 25.0;

    public function __construct(private GeofenceService $geofence)
    {
    }

    /**
     * @param  array  $incidents  Normalized incident arrays (see UsgsEarthquakeFeedClient/GdacsFeedClient).
     * @return array{created: int, updated: int, skipped: int}
     */
    public function ingest(array $incidents): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($incidents as $incident) {
            $counts[$this->ingestOne($incident)]++;
        }

        return $counts;
    }

    private function ingestOne(array $incident): string
    {
        try {
            return DB::transaction(function () use ($incident) {
                $existing = Disaster::where('source', $incident['source'])
                    ->where('external_id', $incident['external_id'])
                    ->first();

                if ($existing) {
                    return $this->updateExisting($existing, $incident);
                }

                $duplicate = $this->findNearDuplicate($incident);
                if ($duplicate) {
                    $duplicate->update([
                        'source' => $incident['source'],
                        'external_id' => $incident['external_id'],
                        'source_url' => $incident['source_url'],
                        'external_updated_at' => $incident['external_updated_at'],
                    ]);

                    return 'updated';
                }

                $disaster = Disaster::create([
                    'name' => $incident['name'],
                    'type' => $incident['type'],
                    'signal' => $incident['signal'],
                    'zone' => $incident['zone'],
                    'lat' => $incident['lat'],
                    'lng' => $incident['lng'],
                    'status' => 'ACTIVE',
                    'source' => $incident['source'],
                    'external_id' => $incident['external_id'],
                    'source_url' => $incident['source_url'],
                    'external_updated_at' => $incident['external_updated_at'],
                ]);

                broadcast(new DisasterAlertEvent($disaster));

                return 'created';
            });
        } catch (QueryException $e) {
            // Unique(source, external_id) violation from an overlapping run
            // (e.g. scheduler tick racing a manual "Run now") — safe to skip.
            Log::warning('Disaster feed ingest skipped a race', [
                'incident' => $incident,
                'message' => $e->getMessage(),
            ]);

            return 'skipped';
        }
    }

    private function updateExisting(Disaster $existing, array $incident): string
    {
        $incomingUpdated = $incident['external_updated_at'];

        // Compared at whole-second precision — sub-second precision (e.g.
        // USGS's millisecond `updated` field) doesn't survive a DB round-trip
        // on the stored side, so comparing finer than seconds would treat an
        // unchanged incident as "newer" on every single poll.
        if (
            $incomingUpdated !== null
            && $existing->external_updated_at !== null
            && $incomingUpdated->timestamp <= $existing->external_updated_at->timestamp
        ) {
            return 'skipped';
        }

        $signalIncreased = $incident['signal'] > $existing->signal;

        // status is intentionally never overwritten here — a coordinator may
        // have already moved this incident to MONITORING/RESOLVED, and the
        // feed has no knowledge of that local response state.
        $existing->update([
            'name' => $incident['name'],
            'signal' => $incident['signal'],
            'lat' => $incident['lat'],
            'lng' => $incident['lng'],
            'zone' => $incident['zone'],
            'source_url' => $incident['source_url'],
            'external_updated_at' => $incident['external_updated_at'],
        ]);

        if ($signalIncreased) {
            broadcast(new DisasterAlertEvent($existing));
        }

        return 'updated';
    }

    /**
     * Finds a manually-logged (source=null), same-type, non-resolved
     * incident within NEAR_DUPLICATE_RADIUS_KM — prevents the feed from
     * creating a duplicate of something a coordinator already entered by hand.
     */
    private function findNearDuplicate(array $incident): ?Disaster
    {
        $lookbackDays = config('services.disaster_feed.lookback_days', 3);

        $candidates = Disaster::query()
            ->whereNull('source')
            ->where('type', $incident['type'])
            ->where('status', '!=', 'RESOLVED')
            ->where('created_at', '>=', now()->subDays($lookbackDays))
            ->get();

        foreach ($candidates as $candidate) {
            $distanceKm = $this->geofence->distanceKm(
                $candidate->lat,
                $candidate->lng,
                $incident['lat'],
                $incident['lng'],
            );

            if ($distanceKm <= self::NEAR_DUPLICATE_RADIUS_KM) {
                return $candidate;
            }
        }

        return null;
    }
}
