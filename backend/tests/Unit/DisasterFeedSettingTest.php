<?php

namespace Tests\Unit;

use App\Models\DisasterFeedSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisasterFeedSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_due_when_no_source_is_enabled(): void
    {
        $settings = DisasterFeedSetting::current();
        $settings->update(['usgs_enabled' => false, 'gdacs_enabled' => false]);

        $this->assertFalse($settings->dueToPoll());
    }

    public function test_due_on_first_run_when_a_source_is_enabled(): void
    {
        $settings = DisasterFeedSetting::current();
        $settings->update(['usgs_enabled' => true, 'last_run_at' => null]);

        $this->assertTrue($settings->dueToPoll());
    }

    public function test_not_due_before_the_interval_elapses(): void
    {
        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => now()->subMinutes(30),
        ]);

        $this->assertFalse($settings->dueToPoll());
    }

    public function test_due_once_the_interval_has_elapsed(): void
    {
        $settings = DisasterFeedSetting::current();
        $settings->update([
            'usgs_enabled' => true,
            'poll_interval_minutes' => 60,
            'last_run_at' => now()->subMinutes(61),
        ]);

        $this->assertTrue($settings->dueToPoll());
    }
}
