<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One lent-out unit in the borrowed-equipment logbook (system-spec.md §3.3).
 * The borrower's name and contact are personal data: hidden by default and
 * shown only to the Admin and Logistics (NPC Circular 2023-06 safeguards).
 */
class Borrow extends Model
{
    use HasUuids;

    protected $table = 'app_borrows';

    protected $fillable = ['request_line_id', 'resource_type_id', 'depot_id', 'property_number', 'borrower_division', 'borrower_name', 'borrower_contact', 'issued_at', 'status'];

    protected $hidden = ['borrower_name', 'borrower_contact'];

    protected $casts = ['issued_at' => 'datetime'];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(StatusChange::class, 'subject_id')->where('subject_type', 'borrow')->orderBy('created_at');
    }
}
