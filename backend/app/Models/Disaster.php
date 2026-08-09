<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Disaster extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_disasters';

    protected $fillable = [
        'name',
        'type',
        'signal',
        'zone',
        'lat',
        'lng',
        'status',
        'source',
        'external_id',
        'source_url',
        'external_updated_at',
    ];

    protected $casts = [
        'signal' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'external_updated_at' => 'datetime',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    /**
     * `app_requests` has no `disaster_id` column — it links to a disaster
     * only indirectly through `app_zones.disaster_id`.
     */
    public function requests(): HasManyThrough
    {
        return $this->hasManyThrough(ResourceRequest::class, Zone::class, 'disaster_id', 'zone_id');
    }

    public function optimizationRuns(): HasMany
    {
        return $this->hasMany(OptimizationRun::class);
    }

    public function evacuationCenters(): HasMany
    {
        return $this->hasMany(EvacuationCenter::class);
    }

    /**
     * Sums each zone's NDRRMC/Sendai-standard indicator fields into a
     * disaster-level total, for the SitRep report (see
     * Services\Reports\NdrrmcReportBuilder). Zone level stays the single
     * source of truth — this is a derived rollup, not stored separately.
     *
     * @return array{casualties_dead: int, casualties_injured: int, casualties_missing: int, families_affected: int, houses_totally_damaged: int, houses_partially_damaged: int, estimated_damage_cost: float}
     */
    public function ndrrmcSummary(): array
    {
        $zones = $this->zones;

        return [
            'casualties_dead' => (int) $zones->sum('casualties_dead'),
            'casualties_injured' => (int) $zones->sum('casualties_injured'),
            'casualties_missing' => (int) $zones->sum('casualties_missing'),
            'families_affected' => (int) $zones->sum('families_affected'),
            'houses_totally_damaged' => (int) $zones->sum('houses_totally_damaged'),
            'houses_partially_damaged' => (int) $zones->sum('houses_partially_damaged'),
            'estimated_damage_cost' => (float) $zones->sum('estimated_damage_cost'),
        ];
    }
}
