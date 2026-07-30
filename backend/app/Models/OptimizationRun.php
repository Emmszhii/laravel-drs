<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One row per call to the Python solver-service — keeps an audit trail
 * of the algorithm used, its objective value, and the raw response,
 * which matters for the thesis evaluation/results chapter.
 */
class OptimizationRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'disaster_id',
        'triggered_by',
        'algorithm_used',
        'objective_value',
        'status',
        'response_payload',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'objective_value' => 'float',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
}
