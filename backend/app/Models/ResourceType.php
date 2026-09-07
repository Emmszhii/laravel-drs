<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_resource_types';

    protected $fillable = ['name', 'unit', 'is_consumable'];

    protected $casts = [
        'is_consumable' => 'boolean',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ResourceRequest::class);
    }
}
