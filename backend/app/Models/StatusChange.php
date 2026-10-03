<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Who moved a request or a borrowed item to which status, and when. */
class StatusChange extends Model
{
    use HasUuids;

    protected $table = 'app_status_changes';

    public $timestamps = false;

    protected $fillable = ['subject_type', 'subject_id', 'from_status', 'to_status', 'user_id', 'note', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    /** Shown beside each change as "by name (position)": the digital stand-in for the form's signature lines. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $type, string $id, ?string $from, string $to, ?string $userId, ?string $note = null): void
    {
        static::create(['subject_type' => $type, 'subject_id' => $id, 'from_status' => $from, 'to_status' => $to,
            'user_id' => $userId, 'note' => $note, 'created_at' => now()]);
    }
}
