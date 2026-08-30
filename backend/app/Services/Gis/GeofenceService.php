<?php

namespace App\Services\Gis;

use App\Models\Depot;
use App\Models\RoadClosure;
use App\Models\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PostGIS-backed (with pure-PHP fallback) helper logic for distance
 * calculations and depot/zone/road-closure geofencing.
 */
class GeofenceService
{
    /**
     * Distance in kilometers. Tries a native ST_Distance geography query on
     * Postgres+PostGIS; falls back to haversine on any failure (extension
     * missing, connection error) or when not on Postgres at all — this
     * method takes raw lat/lng floats so it works regardless of whether the
     * caller's model has a populated `location` geography column.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            try {
                $row = DB::selectOne(
                    'select ST_Distance(
                        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
                    ) / 1000 as km',
                    [$lng1, $lat1, $lng2, $lat2]
                );

                if ($row !== null) {
                    return (float) $row->km;
                }
            } catch (\Throwable $e) {
                // fall through to haversine below
            }
        }

        return $this->haversineKm($lat1, $lng1, $lat2, $lng2);
    }

    /**
     * Haversine distance in kilometers — fallback for when PostGIS isn't
     * available or the ST_Distance query above fails.
     */
    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * MVP post-hoc closure check: does any active RoadClosure fall within
     * its radius of any point along the given route? `routePoints` is a
     * list of [lat, lng] pairs (e.g. a decoded OSRM route geometry). Returns
     * the first matching closure, or null if the route looks clear.
     *
     * Uses ST_DWithin on Postgres (fast, index-friendly); falls back to a
     * plain haversine loop otherwise. This does not reroute — it only
     * flags "route may be blocked" per algorithm.md's explicitly-scoped MVP.
     */
    public function routeCrossesClosure(array $routePoints, ?\DateTimeInterface $at = null): ?RoadClosure
    {
        $at ??= now();

        $closures = RoadClosure::query()->where('is_active', true)->get()
            ->filter(fn (RoadClosure $c) => $c->isActiveAt($at));

        if ($closures->isEmpty() || $routePoints === []) {
            return null;
        }

        $usePostgis = DB::connection()->getDriverName() === 'pgsql';

        foreach ($closures as $closure) {
            foreach ($routePoints as [$lat, $lng]) {
                if ($usePostgis) {
                    try {
                        $row = DB::selectOne(
                            'select ST_DWithin(
                                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                                ?
                            ) as hit',
                            [$lng, $lat, $closure->lng, $closure->lat, $closure->radius_m]
                        );

                        if ($row !== null && (bool) $row->hit) {
                            return $closure;
                        }

                        continue;
                    } catch (\Throwable $e) {
                        // fall through to haversine below for this point
                    }
                }

                if ($this->haversineKm($lat, $lng, $closure->lat, $closure->lng) * 1000 <= $closure->radius_m) {
                    return $closure;
                }
            }
        }

        return null;
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
