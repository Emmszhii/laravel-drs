<?php

namespace App\Services\Replenishment;

use App\Models\HistoryLine;
use App\Models\IcsRequestLine;
use Illuminate\Support\Carbon;

/**
 * Turns encoded request lines into quarterly use per item, oldest first,
 * for every quarter from the first to the last encoded one before the
 * quarter being planned. Within that span, an item with no lines in a
 * quarter counts as zero use.
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
        $before = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->toDateString();
        $encoded = HistoryLine::query()
            ->where('date', '<', $before)
            ->get(['resource_type_id', 'date', 'quantity_requested', 'quantity_released', 'event_tag']);

        // Requests filed in the system feed back as new history, so the multipliers and
        // scenarios update every quarter. Released counts only once a request is closed.
        $live = IcsRequestLine::query()
            ->join('app_ics_requests as r', 'r.id', '=', 'app_ics_request_lines.ics_request_id')
            ->whereNotIn('r.status', ['draft', 'cancelled'])
            ->where('r.date', '<', $before)
            ->get(['app_ics_request_lines.resource_type_id', 'r.date', 'app_ics_request_lines.quantity_requested',
                'app_ics_request_lines.quantity_released', 'r.event_tag', 'r.status'])
            ->map(fn ($l) => (object) [
                'resource_type_id' => $l->resource_type_id,
                'date' => Carbon::parse($l->date),
                'quantity_requested' => (int) $l->quantity_requested,
                'quantity_released' => $l->status === 'closed' ? (int) $l->quantity_released : null,
                'event_tag' => $l->event_tag,
            ]);

        $lines = $encoded->toBase()->merge($live);

        if ($lines->isEmpty()) {
            return ['quarters' => [], 'use' => [], 'tagged' => [], 'event_quarters' => [], 'stockout_quarters' => []];
        }

        // ponytail: one shared start (the earliest encoded quarter) for every item, so an item
        // added to the catalog later reads as zero use before then; per-item start dates if that skews targets.
        $quarterIndexes = $lines->map(fn ($l) => self::index($l->date->year, $l->date->quarter));
        $first = $quarterIndexes->min();
        // Trailing quarters with no record for any item are not encoded yet, not zero use.
        $count = $quarterIndexes->max() + 1 - $first;

        $use = [];
        $tagged = [];
        $events = [];
        $stockouts = [];
        foreach ($lines as $line) {
            $i = self::index($line->date->year, $line->date->quarter) - $first;
            $use[$line->resource_type_id] ??= array_fill(0, $count, 0);
            $use[$line->resource_type_id][$i] += $line->quantity_requested;
            if ($line->event_tag) {
                $tagged[$line->resource_type_id][$i] = true;
                $events[$i] = true;
            }
            // Released below requested: the office ran short, so recorded use understates need.
            if ($line->quantity_released !== null && $line->quantity_released < $line->quantity_requested) {
                $stockouts[$i] = true;
            }
        }

        $quarters = [];
        for ($i = 0; $i < $count; $i++) {
            $quarters[] = [intdiv($first + $i, 4), ($first + $i) % 4 + 1];
        }

        return [
            'quarters' => $quarters,
            'use' => $use,
            'tagged' => $tagged,
            'event_quarters' => array_keys($events),
            'stockout_quarters' => array_keys($stockouts),
        ];
    }

    public static function index(int $year, int $quarter): int
    {
        return $year * 4 + $quarter - 1;
    }
}
