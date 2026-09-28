<?php

namespace App\Services\Replenishment;

use App\Models\DataCheckFlag;
use App\Models\StockCount;
use App\Services\Stock\StockLedger;

/**
 * Pre-solve data checks (system-spec.md §4.2). Rule-based on purpose: the
 * history is too short for anomaly detection. A flag already raised for the
 * same rule, item and quarter is never raised again, so a confirmed entry
 * stays confirmed.
 *   1. use_spike      — a quarter's use is more than 3x the item's usual, with no event tagged;
 *   2. negative_stock — recorded stock is below zero;
 *   3. count_mismatch — recorded stock differs from the latest weekly physical count.
 */
class DataChecks
{
    public const SPIKE_FACTOR = 3;

    public function __construct(private readonly QuarterHistory $history, private readonly StockLedger $ledger)
    {
    }

    /** Raises new flags for the history behind the given plan quarter; returns how many are open. */
    public function run(int $year, int $quarter): int
    {
        $this->useSpikes($year, $quarter);
        $this->negativeStock();
        $this->countMismatches();

        return DataCheckFlag::where('status', 'open')->count();
    }

    private function useSpikes(int $year, int $quarter): void
    {
        $h = $this->history->build($year, $quarter);

        foreach ($h['use'] as $typeId => $uses) {
            foreach ($uses as $i => $use) {
                // "Usual" is the average over the other quarters the item was used at all, so
                // an item that sits idle for a while doesn't make its busy quarters look like spikes.
                $others = $uses;
                unset($others[$i]);
                $others = array_filter($others);
                $usual = $others ? array_sum($others) / count($others) : 0;

                if ($usual > 0 && $use > self::SPIKE_FACTOR * $usual && empty($h['tagged'][$typeId][$i])) {
                    [$y, $q] = $h['quarters'][$i];
                    $this->flag('use_spike', $typeId, $y, $q, sprintf('Use of %d is more than %d times the usual %.1f per quarter, with no event tagged.', $use, self::SPIKE_FACTOR, $usual));
                }
            }
        }
    }

    private function negativeStock(): void
    {
        foreach ($this->ledger->levels() as $row) {
            if ((int) $row->quantity < 0) {
                $this->flag('negative_stock', $row->resource_type_id, now()->year, now()->quarter, "Recorded stock at a depot is {$row->quantity}.");
            }
        }
    }

    private function countMismatches(): void
    {
        $latest = StockCount::query()->orderByDesc('counted_at')->get()->unique(fn ($c) => $c->resource_type_id . '|' . $c->depot_id);

        foreach ($latest as $count) {
            $recorded = $this->ledger->level($count->resource_type_id, $count->depot_id, $count->counted_at);
            if ($recorded !== $count->counted_quantity) {
                $this->flag('count_mismatch', $count->resource_type_id, $count->counted_at->year, $count->counted_at->quarter,
                    "Recorded stock {$recorded} differs from the physical count of {$count->counted_quantity} on {$count->counted_at->toDateString()}.");
            }
        }
    }

    private function flag(string $rule, string $typeId, int $year, int $quarter, string $detail): void
    {
        DataCheckFlag::firstOrCreate(
            ['rule' => $rule, 'resource_type_id' => $typeId, 'year' => $year, 'quarter' => $quarter],
            ['detail' => $detail],
        );
    }
}
