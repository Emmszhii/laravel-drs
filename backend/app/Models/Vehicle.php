<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A vehicle homed at a depot, with a known capacity — feeds
 * OptimizationServiceClient::optimizeRouting()'s CVRPTW vehicle list.
 * Depots without any Vehicle rows yet fall back to a single synthesized
 * vehicle in that method, so routing still works before a fleet is entered.
 */
class Vehicle extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_vehicles';

    protected $fillable = [
        'label',
        'capacity',
        'depot_id',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
