<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Services\DisasterFeed\DisasterFeedIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisasterFeedIngestorTest extends TestCase
{
    use RefreshDatabase;

    private function usgsIncident(array $overrides = []): array
    {
        return array_merge([
            'source' => 'usgs',
            'external_id' => 'us7000test1',
            'name' => 'M 5.0 Earthquake — Test Place',
            'type' => 'Earthquake',
            'signal' => 3,
            'zone' => 'Test Place, Philippines',
            'lat' => 13.2,
            'lng' => 123.8,
            'source_url' => 'https://earthquake.usgs.gov/earthquakes/eventpage/us7000test1',
            'external_updated_at' => Carbon::now(),
        ], $overrides);
    }

    public function test_creates_a_new_disaster_from_a_feed_incident(): void
    {
        $ingestor = app(DisasterFeedIngestor::class);

        $counts = $ingestor->ingest([$this->usgsIncident()]);

        $this->assertSame(['created' => 1, 'updated' => 0, 'skipped' => 0], $counts);
        $this->assertDatabaseHas('app_disasters', [
            'source' => 'usgs',
            'external_id' => 'us7000test1',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_reingesting_the_same_incident_does_not_duplicate(): void
    {
        $ingestor = app(DisasterFeedIngestor::class);
        $incident = $this->usgsIncident();

        $ingestor->ingest([$incident]);
        $counts = $ingestor->ingest([$incident]);

        $this->assertSame(1, Disaster::count());
        $this->assertSame(0, $counts['created']);
        // external_updated_at unchanged => skipped, not updated
        $this->assertSame(0, $counts['updated']);
        $this->assertSame(1, $counts['skipped']);
    }

    public function test_a_newer_update_changes_signal_but_not_a_manually_set_status(): void
    {
        $ingestor = app(DisasterFeedIngestor::class);
        $ingestor->ingest([$this->usgsIncident(['signal' => 2, 'external_updated_at' => Carbon::now()->subHour()])]);

        $disaster = Disaster::where('external_id', 'us7000test1')->firstOrFail();
        $disaster->update(['status' => 'MONITORING']);

        $ingestor->ingest([$this->usgsIncident(['signal' => 4, 'external_updated_at' => Carbon::now()])]);

        $disaster->refresh();
        $this->assertSame(4, $disaster->signal);
        $this->assertSame('MONITORING', $disaster->status);
    }

    public function test_adopts_a_nearby_manual_incident_of_the_same_type_instead_of_duplicating(): void
    {
        $manual = Disaster::create([
            'name' => 'Earthquake felt in Legazpi',
            'type' => 'Earthquake',
            'signal' => 2,
            'zone' => 'Legazpi City, Albay',
            'lat' => 13.21,
            'lng' => 123.85,
            'status' => 'ACTIVE',
        ]);

        $ingestor = app(DisasterFeedIngestor::class);
        $counts = $ingestor->ingest([$this->usgsIncident(['lat' => 13.213, 'lng' => 123.856])]);

        $this->assertSame(1, Disaster::count());
        $this->assertSame(['created' => 0, 'updated' => 1, 'skipped' => 0], $counts);

        $manual->refresh();
        $this->assertSame('usgs', $manual->source);
        $this->assertSame('us7000test1', $manual->external_id);
    }

    public function test_gdacs_event_ids_do_not_collide_across_event_types(): void
    {
        $ingestor = app(DisasterFeedIngestor::class);

        $counts = $ingestor->ingest([
            [
                'source' => 'gdacs',
                'external_id' => 'TC-1000987',
                'name' => 'Cyclone Test (TC — Orange alert)',
                'type' => 'Typhoon',
                'signal' => 3,
                'zone' => 'Philippines',
                'lat' => 12.0,
                'lng' => 124.0,
                'source_url' => null,
                'external_updated_at' => Carbon::now(),
            ],
            [
                'source' => 'gdacs',
                'external_id' => 'FL-1000987',
                'name' => 'Flood Test (FL — Orange alert)',
                'type' => 'Flood',
                'signal' => 3,
                'zone' => 'Philippines',
                'lat' => 12.0,
                'lng' => 124.0,
                'source_url' => null,
                'external_updated_at' => Carbon::now(),
            ],
        ]);

        $this->assertSame(['created' => 2, 'updated' => 0, 'skipped' => 0], $counts);
        $this->assertSame(2, Disaster::count());
    }
}
