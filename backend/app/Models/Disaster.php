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

}
