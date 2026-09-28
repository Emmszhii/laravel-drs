<?php

namespace App\Services\Stock;

use App\Models\Reservation;

/**
 * Stock held for approved requests, so it can't be promised twice.
 * Available stock = on hand (sum of stock entries) - reserved.
 */
class Reservations
{
    /** @return array<string, int> reserved quantity per resource_type_id */
    public static function heldByItem(): array
    {
        return Reservation::query()
            ->selectRaw('resource_type_id, SUM(quantity) AS reserved')
            ->groupBy('resource_type_id')
            ->pluck('reserved', 'resource_type_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public static function heldAt(string $resourceTypeId, string $depotId): int
    {
        return (int) Reservation::where('resource_type_id', $resourceTypeId)->where('depot_id', $depotId)->sum('quantity');
    }
}
