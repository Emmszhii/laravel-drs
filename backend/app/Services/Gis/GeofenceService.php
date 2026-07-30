<?php

namespace App\Services\Gis;

use App\Models\Depot;
use App\Models\Zone;
use Illuminate\Support\Collection;

/**
 * PostGIS-backed (or pure-PHP fallback) helper logic for distance
 * calculations and depot/zone geofencing. Swap the haversine fallback
 * below for a native ST_Distance query once PostGIS geography columns
 * are wired into the `depots`/`zones` migrations.
 */
class GeofenceService
{
    /**
     * Haversine distance in kilometers — fallback for when PostGIS
     * geography columns/queries aren't set up yet.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Returns depots ordered by proximity to the given zone.
     */
    public function nearestDepots(Zone $zone, int $limit = 3): Collection
    {
        return Depot::all()
            ->map(function (Depot $depot) use ($zone) {
                $depot->distance_km = $this->distanceKm(
                    $zone->lat,
                    $zone->lng,
                    $depot->lat,
                    $depot->lng
                );

                return $depot;
            })
            ->sortBy('distance_km')
            ->take($limit)
            ->values();
    }
}
