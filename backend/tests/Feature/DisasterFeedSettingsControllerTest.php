<?php

namespace Tests\Feature;

use App\Jobs\PollDisasterFeedsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DisasterFeedSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_forbidden_from_the_settings_page(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->get('/settings/disaster-feed')
            ->assertStatus(403);
    }

    public function test_viewer_is_forbidden_from_the_api(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->getJson('/api/disaster-feed-settings')
            ->assertStatus(403);
    }

    public function test_admin_can_view_and_update_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/settings/disaster-feed')
            ->assertOk();

        $this->actingAs($admin)
            ->patchJson('/api/disaster-feed-settings', [
                'usgs_enabled' => true,
                'usgs_min_magnitude' => 5.0,
                'gdacs_enabled' => false,
                'gdacs_min_alert_level' => 'Orange',
                'poll_interval_minutes' => 30,
                'auto_resolve_enabled' => true,
            ])
            ->assertOk()
            ->assertJsonFragment(['usgs_enabled' => true, 'poll_interval_minutes' => 30]);
    }

    public function test_invalid_poll_interval_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patchJson('/api/disaster-feed-settings', [
                'usgs_enabled' => true,
                'usgs_min_magnitude' => 5.0,
                'gdacs_enabled' => false,
                'gdacs_min_alert_level' => 'Orange',
                'poll_interval_minutes' => 45, // not one of the fixed options
                'auto_resolve_enabled' => true,
            ])
            ->assertStatus(422);
    }

    public function test_run_now_queues_the_poll_job(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson('/api/disaster-feed-settings/run')
            ->assertStatus(202);

        Queue::assertPushed(PollDisasterFeedsJob::class, fn ($job) => $job->manual === true);
    }
}
