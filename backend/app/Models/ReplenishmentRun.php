<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * One solver run for a quarter. queued → completed → approved | rejected
 * (Admin), plus failed and superseded. An approved run is the quarter's
 * purchase plan; it never touches stock or reservations.
 */
class ReplenishmentRun extends Model
{
    use HasUuids;

    protected $table = 'app_replenishment_runs';

    protected $fillable = [
        'year',
        'quarter',
        'budget',
        'rule',
        'status',
        'inputs',
        'result',
        'error',
        'run_by',
        'decided_by',
        'decided_at',
        'decision_comment',
    ];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'budget' => 'float',
        'inputs' => 'array',
        'result' => 'array',
        'decided_at' => 'datetime',
    ];
}
