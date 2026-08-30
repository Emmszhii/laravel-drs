<?php

namespace Tests\Feature;

use App\Models\DisasterFeedSetting;
use App\Services\DisasterFeed\UsgsEarthquakeFeedClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UsgsEarthquakeFeedClientTest extends TestCase
{
    use RefreshDatabase;

    protected function settings(array $overrides = []): DisasterFeedSetting
    {
        return DisasterFeedSetting::create(array_merge([
            'usgs_enabled' => true,
            'usgs_min_magnitude' => 4.5,
            'gdacs_enabled' => false,
            'poll_interval_minutes' => 60,
        ], $overrides));
    }

    protected function usgsFeature(array $overrides = []): array
    {
        return array_merge([
            'id' => 'us7000test1',
            'properties' => [
                'mag' => 5.4,
                'place' => '20km SE of Legazpi City, Philippines',
                'url' => 'https://earthquake.usgs.gov/earthquakes/eventpage/us7000test1',
                'updated' => 1700000000000,
            ],
            'geometry' => [
                'coordinates' => [123.85, 13.10, 10.0],
            ],
        ], $overrides);
    }

    public function test_disabled_source_returns_ok_with_no_incidents_and_does_not_make_a_request(): void
    {
        Http::fake();

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings(['usgs_enabled' => false]));

        $this->assertSame(['ok' => true, 'error' => null, 'incidents' => []], $result);
        Http::assertNothingSent();
    }

    public function test_it_parses_a_valid_geojson_feed_into_normalized_incidents(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [$this->usgsFeature()],
            ]),
        ]);

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertNull($result['error']);
        $this->assertCount(1, $result['incidents']);

        $incident = $result['incidents'][0];
        $this->assertSame('usgs', $incident['source']);
        $this->assertSame('us7000test1', $incident['external_id']);
        $this->assertSame('Earthquake', $incident['type']);
        $this->assertSame(13.10, $incident['lat']);
        $this->assertSame(123.85, $incident['lng']);
        $this->assertSame(3, $incident['signal']); // magnitude 5.4 => signal 3
        $this->assertStringContainsString('M 5.4 Earthquake', $incident['name']);
        $this->assertSame('20km SE of Legazpi City, Philippines', $incident['zone']);
        $this->assertSame('https://earthquake.usgs.gov/earthquakes/eventpage/us7000test1', $incident['source_url']);
        $this->assertNotNull($incident['external_updated_at']);
    }

    #[DataProvider('magnitudeSignalProvider')]
    public function test_magnitude_maps_to_the_expected_signal_level(float $magnitude, int $expectedSignal): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response([
                'features' => [$this->usgsFeature(['properties' => array_merge(
                    $this->usgsFeature()['properties'],
                    ['mag' => $magnitude]
                )])],
            ]),
        ]);

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertSame($expectedSignal, $result['incidents'][0]['signal']);
    }

    public static function magnitudeSignalProvider(): array
    {
        return [
            [7.5, 5],
            [6.2, 4],
            [5.1, 3],
            [4.2, 2],
            [3.0, 1],
        ];
    }

    public function test_a_feature_missing_magnitude_or_coordinates_is_skipped_without_throwing(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response([
                'features' => [
                    $this->usgsFeature(), // valid
                    array_merge($this->usgsFeature(['id' => 'missing-mag']), [
                        'properties' => ['place' => 'nowhere'],
                    ]),
                    array_merge($this->usgsFeature(['id' => 'missing-coords']), [
                        'geometry' => ['coordinates' => null],
                    ]),
                ],
            ]),
        ]);

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $result['incidents']);
        $this->assertSame('us7000test1', $result['incidents'][0]['external_id']);
    }

    public function test_a_response_with_no_features_key_returns_ok_with_no_incidents(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response([]),
        ]);

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertSame([], $result['incidents']);
    }

    public function test_a_connection_exception_returns_usgs_unavailable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertFalse($result['ok']);
        $this->assertSame('usgs_unavailable', $result['error']);
        $this->assertSame([], $result['incidents']);
    }

    public function test_a_failed_http_response_returns_usgs_unavailable(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response(['message' => 'error'], 500),
        ]);

        $result = (new UsgsEarthquakeFeedClient())->fetchRecent($this->settings());

        $this->assertFalse($result['ok']);
        $this->assertSame('usgs_unavailable', $result['error']);
        $this->assertSame([], $result['incidents']);
    }
}
