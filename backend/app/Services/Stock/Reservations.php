<?php

namespace App\Services\Stock;

use App\Models\Resource;

/**
 * Stock held for approved requests, so it can't be promised twice.
 * Available stock = on hand (sum of stock entries) - reserved.
 */
class Reservations
{
    /** @return array<string, int> reserved quantity per resource_type_id */
    public static function heldByItem(): array
    {
        return Resource::query()
            ->selectRaw('resource_type_id, SUM(quantity_reserved) AS reserved')
            ->groupBy('resource_type_id')
            ->pluck('reserved', 'resource_type_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
