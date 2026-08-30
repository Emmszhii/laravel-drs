<?php

namespace Tests\Unit\Models;

use App\Models\SmtpSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SmtpSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $setting = SmtpSetting::create([
            'is_enabled' => 1,
            'port' => '587',
            'last_test_at' => '2026-01-01 00:00:00',
        ]);

        $this->assertIsBool($setting->is_enabled);
        $this->assertIsInt($setting->port);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->last_test_at);
    }

    public function test_password_is_encrypted_at_rest(): void
    {
        $setting = SmtpSetting::create([
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'password' => 'super-secret-password',
        ]);

        $this->assertSame('super-secret-password', $setting->password);

        $raw = \DB::table('app_settings_smtp')->where('id', $setting->id)->value('password');
        $this->assertNotSame('super-secret-password', $raw);
    }

    public function test_current_creates_a_singleton_row_disabled_by_default(): void
    {
        $this->assertSame(0, SmtpSetting::query()->count());

        $setting = SmtpSetting::current();

        $this->assertSame(1, SmtpSetting::query()->count());
        $this->assertFalse($setting->is_enabled);
    }

    public function test_current_returns_the_same_row_on_subsequent_calls(): void
    {
        $first = SmtpSetting::current();
        $second = SmtpSetting::current();

        $this->assertSame($first->id, $second->id);
    }

    public function test_is_configured_requires_enabled_and_a_host(): void
    {
        $setting = SmtpSetting::current();
        $this->assertFalse($setting->isConfigured());

        $setting->update(['is_enabled' => true, 'host' => null]);
        $this->assertFalse($setting->isConfigured());

        $setting->update(['is_enabled' => true, 'host' => 'smtp.example.com']);
        $this->assertTrue($setting->isConfigured());

        $setting->update(['is_enabled' => false]);
        $this->assertFalse($setting->isConfigured());
    }

    public function test_present_never_exposes_the_raw_password(): void
    {
        $setting = SmtpSetting::current();
        $setting->update([
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'password' => 'super-secret-password',
            'from_address' => 'noreply@example.com',
        ]);

        $presented = $setting->present();

        $this->assertArrayNotHasKey('password', $presented);
        $this->assertTrue($presented['password_set']);
        $this->assertSame('smtp.example.com', $presented['host']);
        $this->assertTrue($presented['is_configured']);
    }

    public function test_present_reports_password_not_set_when_absent(): void
    {
        $setting = SmtpSetting::current();

        $this->assertFalse($setting->present()['password_set']);
    }

    public function test_apply_to_mailer_is_a_noop_when_not_configured(): void
    {
        Config::set('mail.default', 'log');
        $setting = SmtpSetting::current(); // not enabled, no host

        $setting->applyToMailer();

        $this->assertSame('log', config('mail.default'));
    }

    public function test_apply_to_mailer_overrides_mail_config_when_configured(): void
    {
        $setting = SmtpSetting::current();
        $setting->update([
            'is_enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'user',
            'password' => 'secret',
            'encryption' => 'ssl',
            'from_address' => 'noreply@example.com',
            'from_name' => 'DRS',
        ]);

        $setting->applyToMailer();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
        $this->assertSame('DRS', config('mail.from.name'));
    }
}
