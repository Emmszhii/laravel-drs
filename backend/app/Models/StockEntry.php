<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One recorded change in stock — append-only, chained by SHA-256 (see
 * App\Services\Stock\StockLedger, the only writer). Eloquent refuses to
 * update or delete a saved entry; a correction is a new entry.
 */
class StockEntry extends Model
{
    use HasUuids;

    protected $table = 'app_stock_entries';

    public $timestamps = false;

    protected $fillable = [
        'seq', 'device_id', 'resource_type_id', 'depot_id', 'quantity', 'reason', 'user_id', 'device',
        'created_at_device', 'received_at', 'priority', 'depends_on_device_id', 'request_line_id',
        'borrow_id', 'event_tag', 'prev_hash', 'hash',
    ];

    protected $casts = [
        'seq' => 'integer',
        'quantity' => 'integer',
        'priority' => 'boolean',
        'created_at_device' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock entries are append-only.'));
        static::deleting(fn () => throw new LogicException('Stock entries are append-only.'));
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
