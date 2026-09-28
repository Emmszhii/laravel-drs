<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QuarterBudget extends Model
{
    use HasUuids;

    protected $table = 'app_quarter_budgets';

    protected $fillable = ['year', 'quarter', 'budget_amount', 'entered_by'];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'budget_amount' => 'float',
    ];
}
