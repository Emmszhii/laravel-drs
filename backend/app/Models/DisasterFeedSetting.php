<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings for the automated disaster/calamity feed — which
 * sources are enabled (USGS earthquakes, GDACS multi-hazard), how often to
 * poll, and whether stale incidents auto-resolve.
 *
 * @see \App\Jobs\PollDisasterFeedsJob for how this drives the scheduled poll.
 * @see \App\Jobs\AutoResolveIncidentsJob for the auto-resolve toggle.
 */
class DisasterFeedSetting extends Model
{
    use HasUuids;

    protected $table = 'app_settings_disaster_feed';

    public const POLL_INTERVALS = [15, 30, 60, 360, 1440];

    protected $fillable = [
        'usgs_enabled',
        'usgs_min_magnitude',
        'gdacs_enabled',
        'gdacs_min_alert_level',
        'poll_interval_minutes',
        'auto_resolve_enabled',
        'last_run_at',
        'last_run_status',
        'last_run_error',
        'last_run_created_count',
        'last_run_updated_count',
    ];

    protected $casts = [
        'usgs_enabled' => 'boolean',
        'usgs_min_magnitude' => 'float',
        'gdacs_enabled' => 'boolean',
        'poll_interval_minutes' => 'integer',
        'auto_resolve_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'last_run_created_count' => 'integer',
        'last_run_updated_count' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'poll_interval_minutes' => 60,
        ]);
    }

    public function anySourceEnabled(): bool
    {
        return $this->usgs_enabled || $this->gdacs_enabled;
    }

    /**
     * Whether the configured polling interval has elapsed since the last
     * run. The scheduler ticks every 5 minutes (the finest of the offered
     * intervals) and calls this to decide whether to actually dispatch —
     * Laravel's Schedule:: only supports fixed frequencies defined in code,
     * not an admin-configurable one, so this check is what makes the
     * dropdown in Settings → Disaster feed actually mean something.
     */
    public function dueToPoll(): bool
    {
        if (! $this->anySourceEnabled()) {
            return false;
        }

        return $this->last_run_at === null
            || $this->last_run_at->addMinutes($this->poll_interval_minutes)->lte(now());
    }

    /**
     * Shape shared by the Inertia page props and the JSON API response.
     */
    public function present(): array
    {
        return [
            'usgs_enabled' => $this->usgs_enabled,
            'usgs_min_magnitude' => $this->usgs_min_magnitude,
            'gdacs_enabled' => $this->gdacs_enabled,
            'gdacs_min_alert_level' => $this->gdacs_min_alert_level,
            'poll_interval_minutes' => $this->poll_interval_minutes,
            'poll_interval_options' => self::POLL_INTERVALS,
            'auto_resolve_enabled' => $this->auto_resolve_enabled,
            'auto_resolve_max_days' => (int) config('services.disaster_feed.auto_resolve_max_days', 3),
            'last_run_at' => $this->last_run_at,
            'last_run_status' => $this->last_run_status,
            'last_run_error' => $this->last_run_error,
            'last_run_created_count' => $this->last_run_created_count,
            'last_run_updated_count' => $this->last_run_updated_count,
        ];
    }
}
