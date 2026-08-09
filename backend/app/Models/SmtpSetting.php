<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings for admin-configurable outgoing SMTP. When not
 * enabled (or missing a host), all mail keeps using the env-sourced
 * defaults in config/mail.php — this only overrides them when explicitly
 * turned on, same "off by default, explicit opt-in" shape as RoutingSetting.
 */
class SmtpSetting extends Model
{
    use HasUuids;

    protected $table = 'app_settings_smtp';

    protected $fillable = [
        'is_enabled',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'last_test_status',
        'last_test_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted',
        'last_test_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => false,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled && filled($this->host);
    }

    /**
     * Shape shared by the Inertia page props and the JSON API response —
     * never exposes the raw password, only whether one is saved.
     */
    public function present(): array
    {
        return [
            'is_enabled' => $this->is_enabled,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password_set' => (bool) $this->password,
            'encryption' => $this->encryption,
            'from_address' => $this->from_address,
            'from_name' => $this->from_name,
            'is_configured' => $this->isConfigured(),
            'last_test_status' => $this->last_test_status,
            'last_test_at' => $this->last_test_at,
        ];
    }

    /**
     * Overrides Laravel's mail config for the rest of this request. No-op
     * when not configured, so mail keeps using the env-sourced defaults
     * (config/mail.php) exactly as before this feature existed.
     *
     * @see \App\Http\Controllers\Api\SmtpSettingController::test() for the
     *      equivalent override built from not-yet-saved form values.
     */
    public function applyToMailer(): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        config([
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->password,
            // config/mail.php has no "encryption" key in Laravel 13 — implicit
            // TLS is selected via the "smtps" scheme, STARTTLS is negotiated
            // automatically when scheme is left blank.
            'mail.mailers.smtp.scheme' => $this->encryption === 'ssl' ? 'smtps' : null,
            'mail.default' => 'smtp',
            'mail.from.address' => $this->from_address ?: config('mail.from.address'),
            'mail.from.name' => $this->from_name ?: config('mail.from.name'),
        ]);
    }
}
