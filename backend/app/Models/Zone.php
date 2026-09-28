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
    ];

    protected $casts = [
        'population_affected' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
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
