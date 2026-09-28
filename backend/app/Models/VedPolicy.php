<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Coverage level (k of M quarters) and priority weight per VED class
 * (Table 4). Editable, since the values stay proposals until Logistics
 * confirms them; the confirming position and date are recorded.
 */
class VedPolicy extends Model
{
    protected $table = 'app_ved_policies';

    protected $primaryKey = 'ved_class';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['ved_class', 'coverage_k', 'coverage_m', 'weight', 'confirmed_by_position', 'confirmed_at'];

    protected $casts = [
        'coverage_k' => 'integer',
        'coverage_m' => 'integer',
        'weight' => 'float',
        'confirmed_at' => 'date',
    ];
}
