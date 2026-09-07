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
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'request_id');
    }

    /** The Logistics/Admin & Training staff member who approved or rejected this request. */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
