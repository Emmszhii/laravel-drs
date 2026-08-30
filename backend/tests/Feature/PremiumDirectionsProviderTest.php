<?php

namespace Tests\Feature;

use App\Services\Directions\PremiumDirectionsProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 5: traffic-aware "best path" selection — driving-traffic profile,
 * alternatives, and picking the lowest traffic-adjusted duration among them.
 */
class PremiumDirectionsProviderTest extends TestCase
{
    protected function mapboxRoute(float $durationTraffic, float $durationTypical, array $congestion = []): array
    {
        return [
            'distance' => 5000,
            'duration' => $durationTraffic,
            'duration_typical' => $durationTypical,
            'geometry' => ['coordinates' => [[121.05, 14.10], [121.06, 14.11]]],
            'legs' => [[
                'annotation' => ['congestion' => $congestion],
                'steps' => [[
                    'maneuver' => ['type' => 'depart', 'location' => [121.05, 14.10]],
                    'name' => 'Main Rd',
                    'distance' => 5000,
                    'duration' => $durationTraffic,
                ]],
            ]],
        ];
    }

    public function test_it_requests_the_driving_traffic_profile_with_alternatives_and_congestion_annotations(): void
    {
        Http::fake([
            '*/directions/v5/mapbox/driving-traffic/*' => Http::response([
                'code' => 'Ok',
                'routes' => [$this->mapboxRoute(300, 250)],
            ]),
        ]);

        (new PremiumDirectionsProvider('https://api.mapbox.com', 'test-key'))
            ->getRoute(14.10, 121.05, 14.11, 121.06);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/directions/v5/mapbox/driving-traffic/')
                && $request['alternatives'] === 'true'
                && $request['annotations'] === 'congestion,duration';
        });
    }

    public function test_it_picks_the_route_with_the_lowest_traffic_adjusted_duration(): void
    {
        Http::fake([
            '*/directions/v5/mapbox/driving-traffic/*' => Http::response([
                'code' => 'Ok',
                'routes' => [
                    $this->mapboxRoute(600, 400, ['heavy', 'heavy']),
                    $this->mapboxRoute(320, 300, ['low', 'moderate']),
                ],
            ]),
        ]);

        $result = (new PremiumDirectionsProvider('https://api.mapbox.com', 'test-key'))
            ->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertTrue($result['ok']);
        $this->assertEquals(320, $result['duration_in_traffic_s']);
        $this->assertSame(['low', 'moderate'], $result['congestion']);
        $this->assertCount(2, $result['routes']);
        $this->assertSame('lowest_traffic_duration', $result['route_choice_reason']);
    }

    public function test_a_single_route_response_is_flagged_as_only_route(): void
    {
        Http::fake([
            '*/directions/v5/mapbox/driving-traffic/*' => Http::response([
                'code' => 'Ok',
                'routes' => [$this->mapboxRoute(300, 280)],
            ]),
        ]);

        $result = (new PremiumDirectionsProvider('https://api.mapbox.com', 'test-key'))
            ->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertSame('only_route', $result['route_choice_reason']);
    }
}
