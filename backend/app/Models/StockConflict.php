<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A conflict flag on a stock entry that was kept, not dropped (system-spec.md
 * §5.3). Logistics resolves it; a correction is recorded as a new entry.
 */
class StockConflict extends Model
{
    use HasUuids;

    protected $table = 'app_stock_conflicts';

    protected $fillable = ['stock_entry_id', 'request_line_id', 'reason', 'detail', 'status', 'resolution_note', 'resolution_entry_id', 'resolved_by', 'resolved_at'];

    protected $casts = ['resolved_at' => 'datetime'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(StockEntry::class, 'stock_entry_id');
    }
}
