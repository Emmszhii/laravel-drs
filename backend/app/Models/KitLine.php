<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KitLine extends Model
{
    use HasUuids;

    protected $table = 'app_kit_lines';

    protected $fillable = ['kit_id', 'resource_type_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];
}
