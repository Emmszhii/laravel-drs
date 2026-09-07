<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A borrowed (non-consumable) item's lifecycle record: who has it, when it's
 * due back, and its current state. Aggregate-level, not per-serialized-unit —
 * matches how CDRRMO's own Excel/logbook tracking already works.
 */
class ResourceLoan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_resource_loans';

    protected $fillable = [
        'resource_id',
        'borrower_name',
        'borrower_contact',
        'quantity',
        'issued_at',
        'due_at',
        'returned_at',
        'status',
        'notes',
        'issued_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'issued_at' => 'datetime',
        'due_at' => 'date',
        'returned_at' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
