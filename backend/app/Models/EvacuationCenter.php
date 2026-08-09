<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvacuationCenter extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_evacuation_centers';

    protected $fillable = [
        'disaster_id',
        'zone_id',
        'name',
        'address',
        'lat',
        'lng',
        'capacity',
        'families_served',
        'individuals_served',
        'status',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'capacity' => 'integer',
        'families_served' => 'integer',
        'individuals_served' => 'integer',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
