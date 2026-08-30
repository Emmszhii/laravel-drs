<?php

namespace Tests\Feature;

use App\Models\DisasterFeedSetting;
use App\Services\DisasterFeed\GdacsFeedClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GdacsFeedClientTest extends TestCase
{
    use RefreshDatabase;

    protected function settings(array $overrides = []): DisasterFeedSetting
    {
        return DisasterFeedSetting::create(array_merge([
            'usgs_enabled' => false,
            'gdacs_enabled' => true,
            'gdacs_min_alert_level' => 'Orange',
            'poll_interval_minutes' => 60,
        ], $overrides));
    }

    protected function gdacsFeature(array $overrides = []): array
    {
        return array_merge([
            'properties' => [
                'eventtype' => 'TC',
                'eventid' => 1000987,
                'eventname' => 'Typhoon Test',
                'country' => 'Philippines',
                'alertlevel' => 'Orange',
                'todate' => '2026-08-20T00:00:00',
                'url' => ['report' => 'https://www.gdacs.org/report/TC1000987'],
                'severitydata' => ['severity' => 120],
            ],
            'geometry' => [
                'coordinates' => [124.0, 12.5],
            ],
        ], $overrides);
    }

    public function test_disabled_source_returns_ok_with_no_incidents_and_does_not_make_a_request(): void
    {
        Http::fake();

        $result = (new GdacsFeedClient())->fetchRecent($this->settings(['gdacs_enabled' => false]));

        $this->assertSame(['ok' => true, 'error' => null, 'incidents' => []], $result);
        Http::assertNothingSent();
    }

    public function test_it_parses_a_valid_feature_collection_into_normalized_incidents(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response([
                'features' => [$this->gdacsFeature()],
            ]),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertNull($result['error']);
        $this->assertCount(1, $result['incidents']);

        $incident = $result['incidents'][0];
        $this->assertSame('gdacs', $incident['source']);
        $this->assertSame('TC-1000987', $incident['external_id']);
        $this->assertSame('Typhoon', $incident['type']);
        $this->assertSame(12.5, $incident['lat']);
        $this->assertSame(124.0, $incident['lng']);
        $this->assertSame('Philippines', $incident['zone']);
        $this->assertSame('https://www.gdacs.org/report/TC1000987', $incident['source_url']);
        $this->assertStringContainsString('Typhoon Test', $incident['name']);
        $this->assertNotNull($incident['external_updated_at']);
        // 120 km/h wind => signal 4 (>= 118)
        $this->assertSame(4, $incident['signal']);
    }

    public function test_earthquake_type_falls_back_to_alert_level_signal_mapping(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response([
                'features' => [$this->gdacsFeature([
                    'properties' => array_merge($this->gdacsFeature()['properties'], [
                        'eventtype' => 'EQ',
                        'eventid' => 555,
                        'alertlevel' => 'Red',
                    ]),
                ])],
            ]),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertSame('Earthquake', $result['incidents'][0]['type']);
        $this->assertSame(4, $result['incidents'][0]['signal']); // Red => 4
    }

    public function test_a_feature_with_an_unmapped_event_type_is_skipped_without_throwing(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response([
                'features' => [
                    $this->gdacsFeature(),
                    $this->gdacsFeature([
                        'properties' => array_merge($this->gdacsFeature()['properties'], [
                            'eventtype' => 'XX',
                            'eventid' => 999,
                        ]),
                    ]),
                ],
            ]),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $result['incidents']);
        $this->assertSame('TC-1000987', $result['incidents'][0]['external_id']);
    }

    public function test_a_feature_missing_coordinates_is_skipped_without_throwing(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response([
                'features' => [
                    $this->gdacsFeature(),
                    $this->gdacsFeature([
                        'properties' => array_merge($this->gdacsFeature()['properties'], ['eventid' => 111]),
                        'geometry' => ['coordinates' => null],
                    ]),
                ],
            ]),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $result['incidents']);
    }

    public function test_a_response_with_a_non_array_features_shape_returns_gdacs_unexpected_shape(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response([
                'features' => 'not-an-array',
            ]),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertFalse($result['ok']);
        $this->assertSame('gdacs_unexpected_shape', $result['error']);
        $this->assertSame([], $result['incidents']);
    }

    public function test_a_connection_exception_returns_gdacs_unavailable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertFalse($result['ok']);
        $this->assertSame('gdacs_unavailable', $result['error']);
        $this->assertSame([], $result['incidents']);
    }

    public function test_a_failed_http_response_returns_gdacs_unavailable(): void
    {
        Http::fake([
            '*gdacs.org*' => Http::response(['message' => 'error'], 500),
        ]);

        $result = (new GdacsFeedClient())->fetchRecent($this->settings());

        $this->assertFalse($result['ok']);
        $this->assertSame('gdacs_unavailable', $result['error']);
        $this->assertSame([], $result['incidents']);
    }
}
