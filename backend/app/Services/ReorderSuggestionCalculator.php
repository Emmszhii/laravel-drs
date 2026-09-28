<?php

namespace App\Services;

use App\Models\QuarterlyStockSnapshot;
use App\Models\Resource;

/**
 * Reorder-point-style advisory calculation over trailing-quarter consumption
 * history — formalizes CDRRMO's existing manual quarterly stock-review
 * practice (2026-09-03 interview). Purely advisory: it never changes
 * allocation or stock behavior, only surfaces a suggested reorder quantity
 * for Logistics staff to act on manually, the same way the decision is made
 * today.
 */
class ReorderSuggestionCalculator
{
    private const SAFETY_FACTOR = 1.5;

    /** @return array{avg_quarterly_consumption: float, suggested_reorder: int, note: ?string} */
    public function suggest(Resource $resource, int $trailingQuarters = 4): array
    {
        $snapshots = QuarterlyStockSnapshot::query()
            ->where('depot_id', $resource->depot_id)
            ->where('resource_type_id', $resource->resource_type_id)
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->limit($trailingQuarters)
            ->get();

        if ($snapshots->isEmpty()) {
            return [
                'avg_quarterly_consumption' => 0.0,
                'suggested_reorder' => 0,
                'note' => 'Insufficient history — no quarterly snapshot recorded yet for this depot/resource type.',
            ];
        }

        $avgConsumption = (float) $snapshots->avg('quantity_consumed');
        $suggested = max(0, (int) round($avgConsumption * self::SAFETY_FACTOR - $resource->quantity_available));

        return [
            'avg_quarterly_consumption' => round($avgConsumption, 2),
            'suggested_reorder' => $suggested,
            'note' => null,
        ];
    }
}
