<?php

namespace Tests\Feature;

use App\Models\RoadClosure;
use App\Services\Gis\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4: distanceKm()/routeCrossesClosure() PostGIS-vs-haversine paths.
 * The test suite runs on SQLite (see phpunit.xml), so these exercise the
 * haversine fallback — the native ST_Distance/ST_DWithin path is verified
 * manually against a real Postgres+PostGIS container (see algorithm.md
 * Phase 4 verification), since SQLite can't run those functions at all.
 */
class GeofenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_distance_km_falls_back_to_haversine_on_non_postgres_connections(): void
    {
        $service = new GeofenceService;

        // Known haversine reference: ~1.546km between these two points.
        $km = $service->distanceKm(14.10, 121.05, 14.11, 121.06);

        $this->assertEqualsWithDelta(1.546, $km, 0.01);
    }

    public function test_route_crosses_closure_flags_a_route_through_an_active_closure(): void
    {
        $service = new GeofenceService;

        $closure = RoadClosure::factory()->create([
            'lat' => 14.1005,
            'lng' => 121.0505,
            'radius_m' => 200,
            'is_active' => true,
        ]);

        $routeThroughClosure = [[14.10, 121.05], [14.1005, 121.0505], [14.11, 121.06]];

        $hit = $service->routeCrossesClosure($routeThroughClosure);

        $this->assertNotNull($hit);
        $this->assertSame($closure->id, $hit->id);
    }

    public function test_route_crosses_closure_returns_null_for_a_route_far_from_any_closure(): void
    {
        $service = new GeofenceService;

        RoadClosure::factory()->create([
            'lat' => 14.1005,
            'lng' => 121.0505,
            'radius_m' => 200,
            'is_active' => true,
        ]);

        $farRoute = [[13.95, 120.95], [13.96, 120.96]];

        $this->assertNull($service->routeCrossesClosure($farRoute));
    }

    public function test_route_crosses_closure_ignores_inactive_closures(): void
    {
        $service = new GeofenceService;

        RoadClosure::factory()->create([
            'lat' => 14.1005,
            'lng' => 121.0505,
            'radius_m' => 200,
            'is_active' => false,
        ]);

        $routeThroughClosure = [[14.1005, 121.0505]];

        $this->assertNull($service->routeCrossesClosure($routeThroughClosure));
    }

    public function test_route_crosses_closure_ignores_closures_outside_their_active_window(): void
    {
        $service = new GeofenceService;

        RoadClosure::factory()->create([
            'lat' => 14.1005,
            'lng' => 121.0505,
            'radius_m' => 200,
            'is_active' => true,
            'active_from' => now()->addDay(),
            'active_until' => now()->addDays(2),
        ]);

        $routeThroughClosure = [[14.1005, 121.0505]];

        $this->assertNull($service->routeCrossesClosure($routeThroughClosure));
    }
}
