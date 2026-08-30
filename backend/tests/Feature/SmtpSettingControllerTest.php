<?php

namespace Tests\Feature;

use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SmtpSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_forbidden_from_the_api(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->getJson('/api/smtp-settings')
            ->assertStatus(403);
    }

    public function test_show_returns_the_lazily_created_default_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/smtp-settings');

        $response->assertOk()->assertJsonFragment([
            'is_enabled' => false,
            'password_set' => false,
            'is_configured' => false,
        ]);

        $this->assertDatabaseHas('app_settings_smtp', ['is_enabled' => false]);
    }

    public function test_admin_can_update_settings_and_password_is_never_exposed_raw(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patchJson('/api/smtp-settings', [
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'notifications@example.com',
            'password' => 'super-secret-password',
            'encryption' => 'tls',
            'from_address' => 'notifications@example.com',
            'from_name' => 'DisasterResource+',
        ]);

        $response->assertOk()
            ->assertJsonPath('is_enabled', true)
            ->assertJsonPath('host', 'smtp.example.com')
            ->assertJsonPath('port', 587)
            ->assertJsonPath('password_set', true)
            ->assertJsonPath('is_configured', true);

        $response->assertJsonMissing(['password' => 'super-secret-password']);

        $settings = SmtpSetting::current();
        $this->assertSame('smtp.example.com', $settings->host);
        $this->assertSame('super-secret-password', $settings->password);
    }

    public function test_update_does_not_overwrite_saved_password_when_submission_is_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $settings = SmtpSetting::current();
        $settings->update([
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'password' => 'existing-secret-password',
        ]);

        $response = $this->actingAs($admin)->patchJson('/api/smtp-settings', [
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'password' => '',
            'from_address' => 'notifications@example.com',
        ]);

        $response->assertOk();

        $this->assertSame('existing-secret-password', $settings->fresh()->password);
    }

    public function test_update_validation_fails_on_invalid_encryption(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patchJson('/api/smtp-settings', [
            'is_enabled' => true,
            'encryption' => 'rot13',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['encryption']);
    }

    public function test_test_action_reports_success_and_sends_mail(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/smtp-settings/test', [
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'notifications@example.com',
            'password' => 'super-secret-password',
            'encryption' => 'tls',
            'from_address' => 'notifications@example.com',
            'from_name' => 'DisasterResource+',
            'to' => 'coordinator@example.com',
        ]);

        $response->assertOk()->assertJsonPath('ok', true);
        $this->assertNotNull($response->json('tested_at'));

        $this->assertDatabaseHas('app_settings_smtp', ['last_test_status' => 'ok']);
    }

    public function test_test_action_reports_failure_when_mail_sending_throws(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('connection refused'));

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/smtp-settings/test', [
            'host' => 'smtp.invalid.example',
            'port' => 587,
            'from_address' => 'notifications@example.com',
            'to' => 'coordinator@example.com',
        ]);

        $response->assertOk()->assertJsonPath('ok', false);

        $this->assertDatabaseHas('app_settings_smtp', ['last_test_status' => 'failed']);
    }

    public function test_test_action_defaults_recipient_to_authenticated_user_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin-recipient@example.com']);

        $response = $this->actingAs($admin)->postJson('/api/smtp-settings/test', [
            'host' => 'smtp.example.com',
            'port' => 587,
            'from_address' => 'notifications@example.com',
        ]);

        $response->assertOk()->assertJsonPath('ok', true);
    }

    public function test_test_action_validation_fails_without_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/smtp-settings/test', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['host', 'port', 'from_address']);
    }
}
