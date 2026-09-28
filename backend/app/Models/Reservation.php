<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Stock held for an approved request line; released or freed, never a stock entry itself. */
class Reservation extends Model
{
    use HasUuids;

    protected $table = 'app_reservations';

    protected $fillable = ['request_line_id', 'resource_type_id', 'depot_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];
}
