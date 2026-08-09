<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One depot-to-destination resource-in-transit trip, with live GPS position
 * updates from the assigned driver (see DispatchController::updatePosition,
 * DispatchPositionUpdated). `allocation_id` is nullable so a dispatch can be
 * logged standalone today and linked into a formal allocation-approval flow
 * later without a schema change.
 */
class Dispatch extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_dispatches';

    protected $fillable = [
        'disaster_id',
        'zone_id',
        'depot_id',
        'allocation_id',
        'resource_type_id',
        'driver_id',
        'vehicle_label',
        'status',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'current_lat',
        'current_lng',
        'last_position_at',
        'dispatched_at',
        'delivered_at',
        'created_by',
    ];

    protected $casts = [
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'current_lat' => 'float',
        'current_lng' => 'float',
        'last_position_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
