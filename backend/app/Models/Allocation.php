<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'request_id',
        'depot_id',
        'optimization_run_id',
        'quantity_allocated',
        'status',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ResourceRequest::class, 'request_id');
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
