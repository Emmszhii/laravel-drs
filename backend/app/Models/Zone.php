<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A geofenced affected area tied to a Disaster — e.g. a barangay-level
 * polygon or point used for population/severity tracking and for the
 * solver-service's distance/coverage calculations.
 *
 * `boundary` is a PostGIS geography(Polygon) or geography(Point) column.
 * Pair with `mstaack/laravel-postgis` or a custom cast if you're not
 * using a spatial-aware ORM package.
 */
class Zone extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_zones';

    protected $fillable = [
        'disaster_id',
        'name',
        'population_affected',
        'severity_level',
        'lat',
        'lng',
        // 'boundary', // geography(Polygon, 4326) — enable once PostGIS casting is wired up
        'casualties_dead',
        'casualties_injured',
        'casualties_missing',
        'families_affected',
        'houses_totally_damaged',
        'houses_partially_damaged',
        'estimated_damage_cost',
        'damage_cost_currency',
    ];

    protected $casts = [
        'population_affected' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'casualties_dead' => 'integer',
        'casualties_injured' => 'integer',
        'casualties_missing' => 'integer',
        'families_affected' => 'integer',
        'houses_totally_damaged' => 'integer',
        'houses_partially_damaged' => 'integer',
        'estimated_damage_cost' => 'float',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ResourceRequest::class);
    }
}
