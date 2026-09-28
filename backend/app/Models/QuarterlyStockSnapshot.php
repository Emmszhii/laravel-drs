<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One depot/resource_type's stock position at a quarter's close, plus how
 * much was consumed (fulfilled) during that quarter — the historical basis
 * ReorderSuggestionCalculator averages over. Written by
 * SnapshotQuarterlyStockJob.
 */
class QuarterlyStockSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_quarterly_stock_snapshots';

    protected $fillable = [
        'depot_id',
        'resource_type_id',
        'year',
        'quarter',
        'quantity_available_at_close',
        'quantity_consumed',
    ];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'quantity_available_at_close' => 'integer',
        'quantity_consumed' => 'integer',
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
