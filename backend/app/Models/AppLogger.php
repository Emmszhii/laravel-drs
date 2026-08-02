<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit-trail row written by App\Support\ActivityLogger. See the
 * `app_loggers` migration for the deliberate no-FK design (subject/actor are
 * disconnected snapshots so history outlives the rows it describes).
 */
class AppLogger extends Model
{
    protected $table = 'app_loggers';

    protected $fillable = [
        'action',
        'loggable_type',
        'loggable_id',
        'subject_label',
        'actor_id',
        'actor',
        'changes',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected $casts = [
        'actor' => 'array',
        'changes' => 'array',
    ];
}
