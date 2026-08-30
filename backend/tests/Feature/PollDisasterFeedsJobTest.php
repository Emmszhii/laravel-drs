<?php

namespace Tests\Feature;

use App\Jobs\PollDisasterFeedsJob;
use App\Models\Disaster;
use App\Models\DisasterFeedSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers PollDisasterFeedsJob::handle() — the actual poll-and-ingest logic,
 * as opposed to DisasterFeedSettingsControllerTest which only asserts the
 * job gets dispatched.
 */
class PollDisasterFeedsJobTest extends TestCase
{
    use RefreshDatabase;

    private function usgsGeojson(): array
    {
        return [
            'features' => [
                [
                    'id' => 'us7000abcd',
                    'properties' => [
                        'mag' => 5.4,
                        'place' => '20km SE of Legazpi, Philippines',
                        'url' => 'https://earthquake.usgs.gov/earthquakes/eventpage/us7000abcd',
                        'updated' => now()->valueOf(),
                    ],
                    'geometry' => [
                        'coordinates' => [123.85, 13.15, 10.0],
                    ],
                ],
            ],
        ];
    }

    private function gdacsGeojson(): array
    {
        return [
            'features' => [
                [
                    'properties' => [
                        'eventtype' => 'TC',
                        'eventid' => 1000123,
                        'eventname' => 'Typhoon Test',
                        'country' => 'Philippines',
                        'alertlevel' => 'Orange',
                        'todate' => now()->toIso8601String(),
                        'url' => ['report' => 'https://gdacs.org/report/1000123'],
                    ],
                    'geometry' => [
                        'coordinates' => [124.0, 12.0],
                    ],
                ],
            ],
        ];
    }

    public function test_handle_ingests_incidents_from_both_feeds_and_records_the_run(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response($this->usgsGeojson()),
            '*gdacs.org*' => Http::response($this->gdacsGeojson()),
        ]);

        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'gdacs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => null,
        ]);

        PollDisasterFeedsJob::dispatchSync();

        $this->assertDatabaseHas('app_disasters', [
            'source' => 'usgs',
            'external_id' => 'us7000abcd',
        ]);
        $this->assertDatabaseHas('app_disasters', [
            'source' => 'gdacs',
            'external_id' => 'TC-1000123',
        ]);
        $this->assertSame(2, Disaster::count());

        $settings->refresh();
        $this->assertNotNull($settings->last_run_at);
        $this->assertSame('ok', $settings->last_run_status);
        $this->assertNull($settings->last_run_error);
        $this->assertSame(2, $settings->last_run_created_count);
        $this->assertSame(0, $settings->last_run_updated_count);
    }

    public function test_handle_records_a_failed_status_when_all_enabled_feeds_error(): void
    {
        // Both feed clients only catch ConnectionException (a transport-level
        // failure) around their Http:: calls — this simulates that, rather
        // than a non-2xx response, which Http::retry()'s default $throw=true
        // would surface as an uncaught RequestException instead.
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'gdacs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => null,
        ]);

        PollDisasterFeedsJob::dispatchSync();

        $this->assertSame(0, Disaster::count());

        $settings->refresh();
        $this->assertSame('failed', $settings->last_run_status);
        $this->assertNotNull($settings->last_run_error);
        $this->assertStringContainsString('usgs:', $settings->last_run_error);
        $this->assertStringContainsString('gdacs:', $settings->last_run_error);
    }

    public function test_handle_skips_cleanly_when_no_feed_source_is_enabled(): void
    {
        Http::fake();

        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => false,
            'gdacs_enabled' => false,
            'last_run_at' => null,
        ]);

        PollDisasterFeedsJob::dispatchSync();

        Http::assertNothingSent();
        $this->assertSame(0, Disaster::count());

        $settings->refresh();
        // dueToPoll() is false when no source is enabled, so handle() returns
        // early before touching last_run_* at all.
        $this->assertNull($settings->last_run_at);
    }

    public function test_handle_skips_when_not_yet_due_to_poll(): void
    {
        Http::fake();

        // startOfSecond() avoids a false mismatch after the DB round-trip
        // truncates microsecond precision on read-back.
        $lastRunAt = now()->subMinutes(5)->startOfSecond();
        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'gdacs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => $lastRunAt,
            'last_run_status' => 'ok',
        ]);

        PollDisasterFeedsJob::dispatchSync();

        Http::assertNothingSent();
        $this->assertSame(0, Disaster::count());

        $settings->refresh();
        // Unchanged — the job returned before updating anything.
        $this->assertSame('ok', $settings->last_run_status);
        $this->assertTrue($settings->last_run_at->equalTo($lastRunAt));
    }

    public function test_manual_run_bypasses_the_due_to_poll_check(): void
    {
        Http::fake([
            '*earthquake.usgs.gov*' => Http::response($this->usgsGeojson()),
            '*gdacs.org*' => Http::response(['features' => []]),
        ]);

        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'gdacs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => now()->subMinutes(5),
        ]);

        PollDisasterFeedsJob::dispatchSync(manual: true);

        $this->assertSame(1, Disaster::count());

        $settings->refresh();
        $this->assertSame('ok', $settings->last_run_status);
        $this->assertSame(1, $settings->last_run_created_count);
    }
}
