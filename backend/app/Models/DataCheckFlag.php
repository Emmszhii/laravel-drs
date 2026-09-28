<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A pre-solve data check hit (system-spec.md §4.2). Logistics confirms or
 * corrects it; the solver can't run while any flag is still open.
 */
class DataCheckFlag extends Model
{
    use HasUuids;

    protected $table = 'app_data_check_flags';

    protected $fillable = ['rule', 'resource_type_id', 'year', 'quarter', 'detail', 'status', 'resolved_by', 'resolved_at'];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }
}
