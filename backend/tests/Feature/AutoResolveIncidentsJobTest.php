<?php

namespace Tests\Feature;

use App\Jobs\AutoResolveIncidentsJob;
use App\Models\Disaster;
use App\Models\DisasterFeedSetting;
use App\Models\ResourceRequest;
use App\Models\Zone;
use App\Services\DisasterFeed\DepotCapacityChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoResolveIncidentsJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.disaster_feed.auto_resolve_max_days' => 3]);
        DisasterFeedSetting::current()->update(['auto_resolve_enabled' => true]);
    }

    private function staleDisaster(): Disaster
    {
        $disaster = Disaster::factory()->create(['status' => 'ACTIVE']);
        $disaster->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();

        return $disaster;
    }

    public function test_resolves_a_stale_incident_with_no_requests(): void
    {
        $disaster = $this->staleDisaster();

        app(AutoResolveIncidentsJob::class)->handle(app(DepotCapacityChecker::class));

        $this->assertSame('RESOLVED', $disaster->fresh()->status);
    }

    public function test_does_not_resolve_an_incident_with_an_open_request(): void
    {
        $disaster = $this->staleDisaster();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        ResourceRequest::factory()->create(['zone_id' => $zone->id, 'status' => 'pending', 'created_at' => now()->subDays(5)]);

        app(AutoResolveIncidentsJob::class)->handle(app(DepotCapacityChecker::class));

        $this->assertSame('ACTIVE', $disaster->fresh()->status);
    }

    public function test_does_not_resolve_when_a_request_was_active_inside_the_window_even_if_fulfilled(): void
    {
        $disaster = $this->staleDisaster();
        $zone = Zone::factory()->create(['disaster_id' => $disaster->id]);
        ResourceRequest::factory()->create([
            'zone_id' => $zone->id,
            'status' => 'fulfilled',
            'created_at' => now()->subDay(),
        ]);

        app(AutoResolveIncidentsJob::class)->handle(app(DepotCapacityChecker::class));

        $this->assertSame('ACTIVE', $disaster->fresh()->status);
    }

    public function test_does_not_resolve_a_recent_incident(): void
    {
        $disaster = Disaster::factory()->create(['status' => 'ACTIVE', 'created_at' => now()]);

        app(AutoResolveIncidentsJob::class)->handle(app(DepotCapacityChecker::class));

        $this->assertSame('ACTIVE', $disaster->fresh()->status);
    }

    public function test_does_nothing_when_auto_resolve_is_disabled(): void
    {
        DisasterFeedSetting::current()->update(['auto_resolve_enabled' => false]);
        $disaster = $this->staleDisaster();

        app(AutoResolveIncidentsJob::class)->handle(app(DepotCapacityChecker::class));

        $this->assertSame('ACTIVE', $disaster->fresh()->status);
    }

}
