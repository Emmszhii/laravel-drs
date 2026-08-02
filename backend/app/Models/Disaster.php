<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'signal' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ResourceRequest::class);
    }

    public function optimizationRuns(): HasMany
    {
        return $this->hasMany(OptimizationRun::class);
    }
}
