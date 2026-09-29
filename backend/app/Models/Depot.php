<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Depot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_depots';

    protected $fillable = [
        'name',
        'address',
        'status',
        'free_space',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
    ];

    protected $casts = [
        'free_space' => 'float', // m³; only used when depot space is modeled (system-spec.md §4.5)
    ];

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }
}
