<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IcsRequestLine extends Model
{
    use HasUuids;

    protected $table = 'app_ics_request_lines';

    protected $fillable = ['ics_request_id', 'resource_type_id', 'depot_id', 'quantity_requested', 'quantity_approved', 'quantity_released'];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved' => 'integer',
        'quantity_released' => 'integer',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(IcsRequest::class, 'ics_request_id');
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'request_line_id');
    }
}
