<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MVP road-closure model: a point + radius + active time window, not a real
 * road-segment geometry — see GeofenceService::routeCrossesClosure() for the
 * post-hoc ST_DWithin check this backs. Real-time OSRM avoidance would need
 * re-running osrm-partition/osrm-customize per closure (minutes, not
 * feasible per-closure) — deliberately out of scope, flagged in algorithm.md.
 */
class RoadClosure extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_road_closures';

    protected $fillable = [
        'label',
        'lat',
        'lng',
        'radius_m',
        'active_from',
        'active_until',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'radius_m' => 'integer',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActiveAt(\DateTimeInterface $at): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->active_from && $at < $this->active_from) {
            return false;
        }

        if ($this->active_until && $at > $this->active_until) {
            return false;
        }

        return true;
    }
}
