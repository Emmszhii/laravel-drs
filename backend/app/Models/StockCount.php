<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** A weekly physical count, compared with recorded stock by data check 3. */
class StockCount extends Model
{
    use HasUuids;

    protected $table = 'app_stock_counts';

    protected $fillable = ['resource_type_id', 'depot_id', 'counted_quantity', 'counted_at', 'counted_by'];

    protected $casts = [
        'counted_quantity' => 'integer',
        'counted_at' => 'datetime',
    ];
}
