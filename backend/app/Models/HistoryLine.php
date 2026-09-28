<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One encoded ICS 213-RR request line from the office's past records — the
 * historical basis the replenishment model reads (system-spec.md §10).
 * Names and signatures are never encoded.
 */
class HistoryLine extends Model
{
    use HasUuids;

    protected $table = 'app_history_lines';

    protected $fillable = [
        'date',
        'resource_type_id',
        'quantity_requested',
        'quantity_released',
        'purpose',
        'event_tag',
        'requesting_division',
        'encoded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity_requested' => 'integer',
        'quantity_released' => 'integer',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }
}
