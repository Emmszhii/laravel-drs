<?php

namespace App\Jobs;

use App\Events\ReplenishmentRunUpdated;
use App\Models\ReplenishmentRun;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\VedPolicy;
use App\Services\Replenishment\QuarterHistory;
use App\Services\Replenishment\ReplenishmentServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Sends each item's history, stock (less reserved), price, space and VED
 * weight to the solver-service and stores the recommendation on the run.
 * The run stays a suggestion until the Admin approves it.
 */
class RunReplenishmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ReplenishmentRun $run)
    {
    }

    public function handle(QuarterHistory $history, ReplenishmentServiceClient $client): void
    {
        $use = $history->build($this->run->year, $this->run->quarter)['use'];
        $policies = VedPolicy::all()->keyBy('ved_class');
        $stock = Resource::query()
            ->selectRaw('resource_type_id, SUM(quantity_available) AS on_hand, SUM(quantity_reserved) AS reserved')
            ->groupBy('resource_type_id')
            ->get()
            ->keyBy('resource_type_id');

        $payload = [
            'rule' => $this->run->rule,
            'budget' => $this->run->budget,
            // ponytail: depot space stays off until Logistics measures it (system-spec.md §4.5).
            'free_space' => null,
            'm' => (int) $policies->max('coverage_m'),
            'items' => ResourceType::orderBy('name')->get()->map(fn (ResourceType $t) => [
                'id' => $t->id,
                'q' => (int) ($stock[$t->id]->on_hand ?? 0),
                'reserved' => (int) ($stock[$t->id]->reserved ?? 0),
                'unit_cost' => $t->unit_cost,
                'space_per_unit' => $t->space_per_unit,
                'weight' => $policies[$t->ved_class]->weight,
                'coverage_k' => $policies[$t->ved_class]->coverage_k,
                'history' => $use[$t->id] ?? [],
            ])->values()->all(),
        ];

        $this->run->update(['inputs' => $payload]);

        try {
            $result = $client->solve($payload);
            if ($result['status'] !== 'infeasible') {
                $curve = $client->budgetCurve($payload);
                $result['budget_curve'] = $curve['points'];
                $result['next_step'] = $curve['next_step'];
            }
        } catch (Throwable $e) {
            $this->run->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 250)]);
            // Live refresh is best-effort: a Reverb outage must not fail the run.
            rescue(fn () => ReplenishmentRunUpdated::dispatch($this->run));

            return;
        }

        $this->run->update([
            'status' => $result['status'] === 'infeasible' ? 'failed' : 'completed',
            'error' => $result['status'] === 'infeasible' ? 'No feasible order.' : null,
            'result' => $result,
        ]);
        // Live refresh is best-effort: a Reverb outage must not fail the run.
        rescue(fn () => ReplenishmentRunUpdated::dispatch($this->run));
    }
}
