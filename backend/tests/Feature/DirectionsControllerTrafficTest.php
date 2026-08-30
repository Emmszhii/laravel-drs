<?php

namespace Tests\Feature;

use App\Models\RoutingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 5 end-to-end: GET /api/directions, through DirectionsRouter, to a
 * premium provider returning multiple traffic-aware alternatives — confirms
 * the chosen route matches the lowest duration_in_traffic_s end-to-end, not
 * just at the PremiumDirectionsProvider unit level.
 */
class DirectionsControllerTrafficTest extends TestCase
{
    use RefreshDatabase;

    protected function mapboxRoute(float $durationTraffic, float $durationTypical, array $congestion): array
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

    public function test_directions_endpoint_returns_the_lowest_traffic_duration_route_from_the_premium_provider(): void
    {
        $user = User::factory()->create();

        RoutingSetting::current()->update([
            'active_provider' => 'premium',
            'premium_base_url' => 'https://api.mapbox.com',
            'premium_api_key' => 'test-key',
        ]);

        Http::fake([
            '*/directions/v5/mapbox/driving-traffic/*' => Http::response([
                'code' => 'Ok',
                'routes' => [
                    $this->mapboxRoute(700, 400, ['severe', 'heavy']),
                    $this->mapboxRoute(310, 300, ['low', 'low']),
                ],
            ]),
        ]);

        $response = $this->actingAs($user)->getJson('/api/directions?'.http_build_query([
            'from_lat' => 14.10,
            'from_lng' => 121.05,
            'to_lat' => 14.11,
            'to_lng' => 121.06,
        ]));

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'provider' => 'premium',
            'duration_in_traffic_s' => 310,
            'route_choice_reason' => 'lowest_traffic_duration',
            'congestion' => ['low', 'low'],
        ]);
    }
}
