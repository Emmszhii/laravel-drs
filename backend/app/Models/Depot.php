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
        'lat',
        'lng',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
