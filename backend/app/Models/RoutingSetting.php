<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings for the directions/routing feature — which provider
 * is active (free self-hosted OSRM vs. an admin-configured paid provider),
 * usage metering against a monthly cap, and auto-fallback state.
 *
 * @see \App\Services\Directions\DirectionsRouter for how this drives provider selection.
 */
class RoutingSetting extends Model
{
    use HasUuids;

    protected $table = 'app_settings_routing';

    protected $fillable = [
        'active_provider',
        'premium_base_url',
        'premium_api_key',
        'premium_monthly_cap',
        'usage_period',
        'usage_count',
        'fallback_triggered_at',
        'fallback_reason',
        'last_test_status',
        'last_test_at',
    ];

    protected $casts = [
        'premium_api_key' => 'encrypted',
        'premium_monthly_cap' => 'integer',
        'usage_count' => 'integer',
        'fallback_triggered_at' => 'datetime',
        'last_test_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'active_provider' => 'osrm',
            'usage_count' => 0,
        ]);
    }

    public function maskedApiKey(): ?string
    {
        if (! $this->premium_api_key) {
            return null;
        }

        return str_repeat('•', 8).substr($this->premium_api_key, -4);
    }

    /**
     * Shape shared by the Inertia page props and the JSON API response —
     * never exposes the raw API key.
     */
    public function present(): array
    {
        return [
            'active_provider' => $this->active_provider,
            'premium_base_url' => $this->premium_base_url,
            'premium_api_key_masked' => $this->maskedApiKey(),
            'premium_monthly_cap' => $this->premium_monthly_cap,
            'usage_period' => $this->usage_period,
            'usage_count' => $this->usage_count,
            'fallback_triggered_at' => $this->fallback_triggered_at,
            'fallback_reason' => $this->fallback_reason,
            'last_test_status' => $this->last_test_status,
            'last_test_at' => $this->last_test_at,
        ];
    }
}
