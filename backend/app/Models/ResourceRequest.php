<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_requests';

    protected $fillable = [
        'zone_id',
        'resource_type_id',
        'quantity_requested',
        'priority',
        'status',
        'requested_by',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'request_id');
    }
}
