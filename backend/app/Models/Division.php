<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A division of the office, or a team inside one (parent_id = its division). */
class Division extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_divisions';

    protected $fillable = ['name', 'parent_id'];

    protected $appends = ['label'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** What a request stores and shows: "Operations and Warning / Team Delta", or the division's own name. */
    public function getLabelAttribute(): string
    {
        return $this->parent_id ? "{$this->parent?->name} / {$this->name}" : $this->name;
    }
}
