<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** One item's quarter from the office's records: what it bought and what it had left at the close. */
class ItemQuarter extends Model
{
    use HasUuids;

    protected $table = 'app_item_quarters';

    protected $fillable = ['resource_type_id', 'year', 'quarter', 'quantity_ordered', 'quantity_available_at_close', 'encoded_by'];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'quantity_ordered' => 'integer',
        'quantity_available_at_close' => 'integer',
    ];
}
