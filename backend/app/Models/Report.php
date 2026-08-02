<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A generated report row — no real PDF pipeline yet, `download()` on the
 * controller streams a synthesized text summary from the linked disaster.
 */
class Report extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_reports';

    protected $fillable = [
        'disaster_id',
        'name',
        'type',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }
}
