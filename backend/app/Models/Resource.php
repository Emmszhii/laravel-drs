<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stock entry: quantity of a given resource type held at a specific depot.
 */
class Resource extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_resources';

    protected $fillable = [
        'depot_id',
        'resource_type_id',
        'quantity_available',
        'quantity_reserved',
        'expiry_date',
    ];

    protected $casts = [
        'quantity_available' => 'integer',
        'quantity_reserved' => 'integer',
        'expiry_date' => 'date',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }
}
