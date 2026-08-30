<?php

namespace Tests\Feature;

use App\Models\RoutingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoutingSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_forbidden_from_the_api(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->getJson('/api/routing-settings')
            ->assertStatus(403);
    }

    public function test_show_returns_the_lazily_created_default_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/routing-settings');

        $response->assertOk()->assertJsonFragment([
            'active_provider' => 'osrm',
            'usage_count' => 0,
            'premium_api_key_masked' => null,
        ]);

        $this->assertDatabaseHas('app_settings_routing', ['active_provider' => 'osrm']);
    }

    public function test_admin_can_update_settings_and_key_is_never_exposed_raw(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patchJson('/api/routing-settings', [
            'active_provider' => 'premium',
            'premium_base_url' => 'https://api.mapbox.example',
            'premium_api_key' => 'sk.supersecretkey1234',
            'premium_monthly_cap' => 5000,
        ]);

        $response->assertOk()
            ->assertJsonPath('active_provider', 'premium')
            ->assertJsonPath('premium_base_url', 'https://api.mapbox.example')
            ->assertJsonPath('premium_monthly_cap', 5000)
            ->assertJsonPath('premium_api_key_masked', '••••••••1234');

        $response->assertJsonMissing(['premium_api_key' => 'sk.supersecretkey1234']);

        $settings = RoutingSetting::current();
        $this->assertSame('premium', $settings->active_provider);
        $this->assertSame('sk.supersecretkey1234', $settings->premium_api_key);
    }

    public function test_update_does_not_overwrite_saved_key_when_submission_is_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $settings = RoutingSetting::current();
        $settings->update([
            'active_provider' => 'premium',
            'premium_api_key' => 'existing-secret-key',
        ]);

        $response = $this->actingAs($admin)->patchJson('/api/routing-settings', [
            'active_provider' => 'premium',
            'premium_base_url' => 'https://api.mapbox.example',
            'premium_api_key' => '',
            'premium_monthly_cap' => 1000,
        ]);

        $response->assertOk();

        $this->assertSame('existing-secret-key', $settings->fresh()->premium_api_key);
    }

    public function test_update_validation_fails_on_invalid_provider(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patchJson('/api/routing-settings', [
            'active_provider' => 'not-a-real-provider',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['active_provider']);
    }

    public function test_test_action_reports_success_when_provider_returns_a_route(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'distance' => 1200.0,
                    'duration' => 300.0,
                    'duration_typical' => 280.0,
                    'geometry' => ['coordinates' => [[123.7515, 13.1449], [123.7438, 13.1391]]],
                    'legs' => [[
                        'annotation' => ['congestion' => ['low']],
                        'steps' => [],
                    ]],
                ]],
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/routing-settings/test', [
            'premium_base_url' => 'https://api.mapbox.example',
            'premium_api_key' => 'sk.testkey',
        ]);

        $response->assertOk()->assertJsonPath('ok', true);
        $this->assertNotNull($response->json('tested_at'));

        $this->assertDatabaseHas('app_settings_routing', ['last_test_status' => 'ok']);
    }

    public function test_test_action_reports_failure_when_provider_call_fails(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'internal error'], 500),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/routing-settings/test', [
            'premium_base_url' => 'https://api.mapbox.example',
            'premium_api_key' => 'sk.testkey',
        ]);

        $response->assertOk()->assertJsonPath('ok', false);

        $this->assertDatabaseHas('app_settings_routing', ['last_test_status' => 'failed']);
    }

    public function test_test_action_reports_failure_on_quota_exceeded(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'rate limit exceeded'], 429),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/routing-settings/test', [
            'premium_base_url' => 'https://api.mapbox.example',
            'premium_api_key' => 'sk.testkey',
        ]);

        $response->assertOk()->assertJsonPath('ok', false);

        $this->assertDatabaseHas('app_settings_routing', ['last_test_status' => 'failed']);
    }

    public function test_test_action_validation_fails_without_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/routing-settings/test', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['premium_base_url', 'premium_api_key']);
    }
}
