<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A digitized ICS 213-RR resource request from one of the office's own
 * divisions (system-spec.md §3.2). Status changes go through
 * App\Services\Requests\RequestWorkflow, which logs each one.
 */
class IcsRequest extends Model
{
    use HasUuids;

    protected $table = 'app_ics_requests';

    protected $fillable = ['requesting_division', 'requested_by', 'date', 'purpose', 'event_tag', 'status', 'decision_reason'];

    protected $casts = ['date' => 'date'];

    public function lines(): HasMany
    {
        return $this->hasMany(IcsRequestLine::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(StatusChange::class, 'subject_id')->where('subject_type', 'ics_request')->orderBy('created_at');
    }
}
