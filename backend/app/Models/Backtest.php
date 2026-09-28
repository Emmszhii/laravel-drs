<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Backtest extends Model
{
    use HasUuids;

    protected $table = 'app_backtests';

    protected $fillable = ['budget', 'quarters', 'inputs', 'result', 'run_by'];

    protected $casts = [
        'budget' => 'float',
        'quarters' => 'array',
        'inputs' => 'array',
        'result' => 'array',
    ];
}
