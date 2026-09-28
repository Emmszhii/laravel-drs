<?php

namespace App\Services\Reports;

use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
use App\Services\Replenishment\QuarterHistory;
use App\Services\Stock\Reservations;
use App\Services\Stock\StockLedger;
use Illuminate\Support\Carbon;

/**
 * Quarterly burn-rate and reorder report (system-spec.md §7), one data shape
 * for both the PDF (dompdf blade view) and the CSV export so the two can't
 * drift apart. On-hand stock is the sum of stock entries; the reorder column
 * is the quarter's approved purchase plan. The latest hash-chain fingerprint
 * is printed on every copy: a chain rebuilt later would no longer match it.
 */
class InventoryBurnRateReportBuilder
{
    /** Burn rate = average use over this many most recent quarters. */
    public const BURN_QUARTERS = 4;

    public function __construct(
        private readonly StockLedger $ledger,
        private readonly QuarterHistory $history,
    ) {
    }

    /** @return array{rows: \Illuminate\Support\Collection, generated_at: Carbon, year: int, quarter: int, plan: ?array, fingerprint: string, records_ok: bool} */
    public function build(?int $year = null, ?int $quarter = null): array
    {
        $year ??= now()->addQuarter()->year;
        $quarter ??= now()->addQuarter()->quarter;

        $use = $this->history->build($year, $quarter)['use'];
        $onHand = $this->ledger->levels()->groupBy('resource_type_id')->map(fn ($r) => (int) $r->sum('quantity'));
        $reserved = Reservations::heldByItem();
        $plan = ReplenishmentRun::where('year', $year)->where('quarter', $quarter)->where('status', 'approved')->latest('decided_at')->first();
        $orders = collect($plan?->result['orders'] ?? [])->keyBy('id');

        $rows = ResourceType::orderBy('name')->get()->map(function (ResourceType $t) use ($use, $onHand, $reserved, $orders) {
            $recent = array_slice($use[$t->id] ?? [], -self::BURN_QUARTERS);
            $burn = $recent ? array_sum($recent) / count($recent) : 0.0;
            $have = $onHand[$t->id] ?? 0;
            $order = $orders[$t->id] ?? null;

            return [
                'resource_type' => $t->name,
                'unit' => $t->unit,
                'on_hand' => $have,
                'reserved' => $reserved[$t->id] ?? 0,
                'burn_rate' => round($burn, 1),
                'quarters_of_cover' => $burn > 0 ? round(($have - ($reserved[$t->id] ?? 0)) / $burn, 2) : null,
                'target' => $order['target'] ?? null,
                'reorder' => $order['x'] ?? null,
                'reason' => $order['reason'] ?? null,
            ];
        });

        $check = $this->ledger->verify();

        return [
            'rows' => $rows,
            'generated_at' => now(),
            'year' => $year,
            'quarter' => $quarter,
            'plan' => $plan ? ['budget' => $plan->budget, 'spent' => $plan->result['spent'] ?? null, 'rule' => $plan->rule, 'approved_at' => $plan->decided_at] : null,
            'fingerprint' => $check['latest_hash'],
            'records_ok' => $check['ok'],
        ];
    }
}
