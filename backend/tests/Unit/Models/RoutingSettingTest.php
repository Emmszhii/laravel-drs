<?php

namespace Tests\Unit\Models;

use App\Models\RoutingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $setting = RoutingSetting::create([
            'active_provider' => 'osrm',
            'premium_monthly_cap' => '1000',
            'usage_count' => '5',
            'fallback_triggered_at' => '2026-01-01 00:00:00',
            'last_test_at' => '2026-01-02 00:00:00',
        ]);

        $this->assertIsInt($setting->premium_monthly_cap);
        $this->assertIsInt($setting->usage_count);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->fallback_triggered_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->last_test_at);
    }

    public function test_premium_api_key_is_encrypted_at_rest(): void
    {
        $setting = RoutingSetting::create([
            'active_provider' => 'premium',
            'premium_api_key' => 'super-secret-key',
        ]);

        $this->assertSame('super-secret-key', $setting->premium_api_key);

        $raw = \DB::table('app_settings_routing')->where('id', $setting->id)->value('premium_api_key');
        $this->assertNotSame('super-secret-key', $raw);
    }

    public function test_current_creates_a_singleton_row_with_defaults(): void
    {
        $this->assertSame(0, RoutingSetting::query()->count());

        $setting = RoutingSetting::current();

        $this->assertSame(1, RoutingSetting::query()->count());
        $this->assertSame('osrm', $setting->active_provider);
        $this->assertSame(0, $setting->usage_count);
    }

    public function test_current_returns_the_same_row_on_subsequent_calls(): void
    {
        $first = RoutingSetting::current();
        $second = RoutingSetting::current();

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, RoutingSetting::query()->count());
    }

    public function test_masked_api_key_returns_null_when_not_set(): void
    {
        $setting = RoutingSetting::current();

        $this->assertNull($setting->maskedApiKey());
    }

    public function test_masked_api_key_hides_all_but_the_last_four_characters(): void
    {
        $setting = RoutingSetting::current();
        $setting->update(['premium_api_key' => 'abcd1234wxyz']);

        $this->assertSame('••••••••wxyz', $setting->maskedApiKey());
    }

    public function test_present_never_exposes_the_raw_api_key(): void
    {
        $setting = RoutingSetting::current();
        $setting->update([
            'premium_api_key' => 'super-secret-key',
            'premium_base_url' => 'https://api.example.com',
            'premium_monthly_cap' => 500,
        ]);

        $presented = $setting->present();

        $this->assertArrayNotHasKey('premium_api_key', $presented);
        $this->assertSame('••••••••-key', $presented['premium_api_key_masked']);
        $this->assertSame('https://api.example.com', $presented['premium_base_url']);
        $this->assertSame(500, $presented['premium_monthly_cap']);
    }
}
