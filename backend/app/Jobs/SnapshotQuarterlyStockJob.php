<?php

namespace App\Jobs;

use App\Models\QuarterlyStockSnapshot;
use App\Models\Resource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Formalizes CDRRMO's quarterly stock-review practice (2026-09-03 interview,
 * Logistics section: "stock levels from one quarter are used to adjust the
 * request for the next quarter"). For each depot/resource_type, records the
 * closing stock position and how much was consumed (fulfilled) during the
 * quarter that just elapsed. Feeds ReorderSuggestionCalculator — advisory
 * only, never touches live allocation behavior. Scheduled quarterly in
 * routes/console.php, so it always runs the day after a quarter closes.
 */
class SnapshotQuarterlyStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function handle(): void
    {
        $closingDate = now()->subDay();
        $year = $closingDate->year;
        $quarter = $closingDate->quarter;
        $start = $closingDate->copy()->firstOfQuarter();
        $end = $closingDate->copy()->lastOfQuarter();

        $consumedByDepotAndType = DB::table('app_allocations')
            ->join('app_requests', 'app_requests.id', '=', 'app_allocations.request_id')
            ->where('app_allocations.status', 'fulfilled')
            ->whereBetween('app_allocations.updated_at', [$start, $end])
            ->selectRaw('app_allocations.depot_id, app_requests.resource_type_id, SUM(app_allocations.quantity_allocated) as total')
            ->groupBy('app_allocations.depot_id', 'app_requests.resource_type_id')
            ->get()
            ->keyBy(fn ($row) => "{$row->depot_id}:{$row->resource_type_id}");

        Resource::query()->orderBy('id')->chunk(200, function ($resources) use ($year, $quarter, $consumedByDepotAndType) {
            foreach ($resources as $resource) {
                $key = "{$resource->depot_id}:{$resource->resource_type_id}";
                $consumed = (int) ($consumedByDepotAndType[$key]->total ?? 0);

                QuarterlyStockSnapshot::updateOrCreate(
                    [
                        'depot_id' => $resource->depot_id,
                        'resource_type_id' => $resource->resource_type_id,
                        'year' => $year,
                        'quarter' => $quarter,
                    ],
                    [
                        'quantity_available_at_close' => $resource->quantity_available,
                        'quantity_consumed' => $consumed,
                    ]
                );
            }
        });
    }
}
