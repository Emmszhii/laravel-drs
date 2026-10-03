<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A named set of request lines (e.g. "Medical backpack #1") that fills a new ICS 213-RR in one click. */
class Kit extends Model
{
    use HasUuids;

    protected $table = 'app_kits';

    protected $fillable = ['name'];

    public function lines(): HasMany
    {
        return $this->hasMany(KitLine::class);
    }
}
