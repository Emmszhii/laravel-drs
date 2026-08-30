<?php

namespace Tests\Feature;

use App\Services\Directions\OsrmDirectionsProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OsrmDirectionsProviderTest extends TestCase
{
    protected function osrmRoute(): array
    {
        return [
            'distance' => 5000.0,
            'duration' => 600.0,
            'geometry' => ['coordinates' => [[121.05, 14.10], [121.06, 14.11]]],
            'legs' => [[
                'steps' => [[
                    'maneuver' => ['type' => 'depart', 'location' => [121.05, 14.10]],
                    'name' => 'Main Rd',
                    'distance' => 5000.0,
                    'duration' => 600.0,
                ]],
            ]],
        ];
    }

    public function test_it_returns_a_normalized_route_on_a_successful_response(): void
    {
        Http::fake([
            '*/route/v1/driving/*' => Http::response([
                'code' => 'Ok',
                'routes' => [$this->osrmRoute()],
            ]),
        ]);

        $result = (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertTrue($result['ok']);
        $this->assertEquals(5000.0, $result['distance_m']);
        $this->assertEquals(600.0, $result['duration_s']);
        $this->assertSame([[121.05, 14.10], [121.06, 14.11]], $result['geometry']);
        $this->assertCount(1, $result['steps']);
        $this->assertSame('Head out on Main Rd', $result['steps'][0]['instruction']);
        $this->assertEquals(5000.0, $result['steps'][0]['distance_m']);
        $this->assertEquals(600.0, $result['steps'][0]['duration_s']);
        $this->assertSame('depart', $result['steps'][0]['maneuver_type']);
        $this->assertNull($result['steps'][0]['maneuver_modifier']);
        $this->assertSame([121.05, 14.10], $result['steps'][0]['location']);
    }

    public function test_it_requests_osrm_with_lng_lat_ordering_and_expected_query_params(): void
    {
        Http::fake([
            '*/route/v1/driving/*' => Http::response([
                'code' => 'Ok',
                'routes' => [$this->osrmRoute()],
            ]),
        ]);

        (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/route/v1/driving/121.05,14.1;121.06,14.11')
                && $request['steps'] === 'true'
                && $request['geometries'] === 'geojson'
                && $request['overview'] === 'full';
        });
    }

    public function test_a_non_ok_response_returns_no_route_found(): void
    {
        Http::fake([
            '*/route/v1/driving/*' => Http::response([
                'code' => 'NoRoute',
                'routes' => [],
            ]),
        ]);

        $result = (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertFalse($result['ok']);
        $this->assertSame('no_route_found', $result['error']);
    }

    public function test_an_ok_response_with_no_routes_returns_no_route_found(): void
    {
        Http::fake([
            '*/route/v1/driving/*' => Http::response([
                'code' => 'Ok',
                'routes' => [],
            ]),
        ]);

        $result = (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertFalse($result['ok']);
        $this->assertSame('no_route_found', $result['error']);
    }

    public function test_a_connection_exception_returns_osrm_unavailable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $result = (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertFalse($result['ok']);
        $this->assertSame('osrm_unavailable', $result['error']);
    }

    public function test_a_failed_http_response_returns_osrm_unavailable(): void
    {
        Http::fake([
            '*/route/v1/driving/*' => Http::response(['message' => 'internal error'], 500),
        ]);

        $result = (new OsrmDirectionsProvider())->getRoute(14.10, 121.05, 14.11, 121.06);

        $this->assertFalse($result['ok']);
        $this->assertSame('osrm_unavailable', $result['error']);
    }

    #[DataProvider('humanizeProvider')]
    public function test_humanize_builds_the_expected_instruction(array $maneuver, string $streetName, string $expected): void
    {
        $this->assertSame($expected, OsrmDirectionsProvider::humanize($maneuver, $streetName));
    }

    public static function humanizeProvider(): array
    {
        return [
            'depart with street' => [['type' => 'depart'], 'Main Rd', 'Head out on Main Rd'],
            'depart without street' => [['type' => 'depart'], '', 'Head out'],
            'arrive' => [['type' => 'arrive'], 'Main Rd', 'Arrive at your destination'],
            'turn left' => [['type' => 'turn', 'modifier' => 'left'], 'Elm St', 'Turn left onto Elm St'],
            'turn sharp left' => [['type' => 'turn', 'modifier' => 'sharp left'], 'Elm St', 'Turn left onto Elm St'],
            'turn slight left' => [['type' => 'turn', 'modifier' => 'slight left'], 'Elm St', 'Turn left onto Elm St'],
            'turn right' => [['type' => 'turn', 'modifier' => 'right'], 'Elm St', 'Turn right onto Elm St'],
            'turn sharp right' => [['type' => 'turn', 'modifier' => 'sharp right'], 'Elm St', 'Turn right onto Elm St'],
            'turn slight right' => [['type' => 'turn', 'modifier' => 'slight right'], 'Elm St', 'Turn right onto Elm St'],
            'turn uturn' => [['type' => 'turn', 'modifier' => 'uturn'], 'Elm St', 'Make a U-turn'],
            'turn default' => [['type' => 'turn', 'modifier' => 'straight'], 'Elm St', 'Continue onto Elm St'],
            'turn no modifier' => [['type' => 'turn'], 'Elm St', 'Continue onto Elm St'],
            'new name with street' => [['type' => 'new name'], 'Elm St', 'Continue onto Elm St'],
            'new name without street' => [['type' => 'new name'], '', 'Continue straight'],
            'merge' => [['type' => 'merge'], 'Elm St', 'Merge onto Elm St'],
            'on ramp' => [['type' => 'on ramp'], 'Elm St', 'Take the ramp onto Elm St'],
            'off ramp' => [['type' => 'off ramp'], 'Elm St', 'Take the exit onto Elm St'],
            'fork left' => [['type' => 'fork', 'modifier' => 'left'], 'Elm St', 'Keep left onto Elm St'],
            'fork slight left' => [['type' => 'fork', 'modifier' => 'slight left'], 'Elm St', 'Keep left onto Elm St'],
            'fork right' => [['type' => 'fork', 'modifier' => 'right'], 'Elm St', 'Keep right onto Elm St'],
            'fork slight right' => [['type' => 'fork', 'modifier' => 'slight right'], 'Elm St', 'Keep right onto Elm St'],
            'fork default' => [['type' => 'fork', 'modifier' => 'straight'], 'Elm St', 'Continue onto Elm St'],
            'end of road left' => [['type' => 'end of road', 'modifier' => 'left'], 'Elm St', 'Turn left onto Elm St'],
            'end of road right' => [['type' => 'end of road', 'modifier' => 'right'], 'Elm St', 'Turn right onto Elm St'],
            'end of road default' => [['type' => 'end of road', 'modifier' => 'straight'], 'Elm St', 'Continue onto Elm St'],
            'roundabout' => [['type' => 'roundabout'], 'Elm St', 'Enter the roundabout onto Elm St'],
            'rotary' => [['type' => 'rotary'], 'Elm St', 'Enter the roundabout onto Elm St'],
            'roundabout turn' => [['type' => 'roundabout turn'], 'Elm St', 'Enter the roundabout onto Elm St'],
            'exit roundabout' => [['type' => 'exit roundabout'], 'Elm St', 'Exit the roundabout onto Elm St'],
            'exit rotary' => [['type' => 'exit rotary'], 'Elm St', 'Exit the roundabout onto Elm St'],
            'continue' => [['type' => 'continue'], 'Elm St', 'Continue onto Elm St'],
            'unknown type with street' => [['type' => 'notify'], 'Elm St', 'Continue on Elm St'],
            'unknown type without street' => [['type' => 'notify'], '', 'Continue'],
        ];
    }
}
