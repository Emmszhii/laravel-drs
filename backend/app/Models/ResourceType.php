<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    use HasFactory, HasUuids;

    /** The "Subject" choices on the paper ICS 213-RR form. A request's subject is the set of its items' categories. */
    public const CATEGORIES = ['medical', 'cleaning', 'office', 'general', 'other'];

    protected $table = 'app_resource_types';

    protected $fillable = ['name', 'unit', 'category', 'is_consumable', 'unit_cost', 'ved_class', 'space_per_unit'];

    protected $casts = [
        'is_consumable' => 'boolean',
        'unit_cost' => 'float',
        'space_per_unit' => 'float',
    ];

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }

    public function historyLines(): HasMany
    {
        return $this->hasMany(HistoryLine::class);
    }
}
