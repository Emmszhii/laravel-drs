<?php

namespace App\Services\Replenishment;

use App\Models\HistoryLine;
use Illuminate\Support\Carbon;

/**
 * Turns encoded request lines into quarterly use per item, oldest first,
 * for every quarter from the first encoded one up to (not including) the
 * quarter being planned. A quarter with no lines counts as zero use.
 *
 * Use is the requested quantity, since released quantities understate need
 * in quarters when stock ran out (Chapter I, Limitations).
 */
class QuarterHistory
{
    /**
     * @return array{quarters: list<array{0:int,1:int}>, use: array<string, list<int>>, tagged: array<string, array<int, bool>>}
     */
    public function build(int $year, int $quarter): array
    {
        $planIndex = self::index($year, $quarter);
        $lines = HistoryLine::query()
            ->where('date', '<', Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->toDateString())
            ->get(['resource_type_id', 'date', 'quantity_requested', 'event_tag']);

        if ($lines->isEmpty()) {
            return ['quarters' => [], 'use' => [], 'tagged' => []];
        }

        // ponytail: one shared start (the earliest encoded quarter) for every item, so an item
        // added to the catalog later reads as zero use before then; per-item start dates if that skews targets.
        $first = $lines->min(fn ($l) => self::index($l->date->year, $l->date->quarter));
        $count = $planIndex - $first;

        $use = [];
        $tagged = [];
        foreach ($lines as $line) {
            $i = self::index($line->date->year, $line->date->quarter) - $first;
            $use[$line->resource_type_id] ??= array_fill(0, $count, 0);
            $use[$line->resource_type_id][$i] += $line->quantity_requested;
            if ($line->event_tag) {
                $tagged[$line->resource_type_id][$i] = true;
            }
        }

        $quarters = [];
        for ($i = 0; $i < $count; $i++) {
            $quarters[] = [intdiv($first + $i, 4), ($first + $i) % 4 + 1];
        }

        return ['quarters' => $quarters, 'use' => $use, 'tagged' => $tagged];
    }

    public static function index(int $year, int $quarter): int
    {
        return $year * 4 + $quarter - 1;
    }
}
