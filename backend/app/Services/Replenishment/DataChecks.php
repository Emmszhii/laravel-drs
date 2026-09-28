<?php

namespace App\Services\Replenishment;

use App\Models\DataCheckFlag;

/**
 * Pre-solve data checks (system-spec.md §4.2). Rule-based on purpose: the
 * history is too short for anomaly detection. A flag already raised for the
 * same item and quarter is never raised again, so a confirmed spike stays
 * confirmed.
 *
 * ponytail: only rule 1 (use spike) runs today. Rules 2 (stock below zero) and
 * 3 (stock vs. weekly physical count) need the append-only stock entries and
 * count records from Sprint 3; add them to run() then.
 */
class DataChecks
{
    public const SPIKE_FACTOR = 3;

    public function __construct(private readonly QuarterHistory $history)
    {
    }

    /** Raises new flags for the history behind the given plan quarter; returns how many are open. */
    public function run(int $year, int $quarter): int
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
                    DataCheckFlag::firstOrCreate(
                        ['rule' => 'use_spike', 'resource_type_id' => $typeId, 'year' => $y, 'quarter' => $q],
                        ['detail' => sprintf('Use of %d is more than %d times the usual %.1f per quarter, with no event tagged.', $use, self::SPIKE_FACTOR, $usual)],
                    );
                }
            }
        }

        return DataCheckFlag::where('status', 'open')->count();
    }
}
