<?php

namespace App\Services\DisasterFeed;

use App\Models\Disaster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The "all resources in the depot is capable" check for auto-resolving an
 * incident — deliberately a plain aggregate comparison, not a solver call,
 * since this only needs to answer "is there any unmet demand this
 * incident's outstanding requests would need" rather than plan an allocation.
 *
 * @see \App\Jobs\AutoResolveIncidentsJob
 */
class DepotCapacityChecker
{
    private const OPEN_REQUEST_STATUSES = ['pending', 'unmet', 'enroute'];

    /**
     * True when every resource type still requested (net of what's already
     * allocated) for this disaster's zones is covered by operational depot
     * stock. True (vacuously) when there is no outstanding demand at all.
     */
    public function canCoverOutstanding(Disaster $disaster): bool
    {
        $outstandingByType = $this->outstandingDemandByResourceType($disaster);

        if ($outstandingByType->isEmpty()) {
            return true;
        }

        $availableByType = $this->availableStockByResourceType();

        foreach ($outstandingByType as $resourceTypeId => $outstandingQty) {
            if ($outstandingQty <= 0) {
                continue;
            }

            $available = $availableByType->get($resourceTypeId, 0);
            if ($available < $outstandingQty) {
                return false;
            }
        }

        return true;
    }

    /** @return Collection<int, int> keyed by resource_type_id */
    private function outstandingDemandByResourceType(Disaster $disaster): Collection
    {
        $zoneIds = $disaster->zones()->pluck('id');

        $requested = DB::table('app_requests')
            ->whereIn('zone_id', $zoneIds)
            ->whereIn('status', self::OPEN_REQUEST_STATUSES)
            ->selectRaw('resource_type_id, SUM(quantity_requested) as total')
            ->groupBy('resource_type_id')
            ->pluck('total', 'resource_type_id');

        $allocated = DB::table('app_allocations')
            ->join('app_requests', 'app_requests.id', '=', 'app_allocations.request_id')
            ->whereIn('app_requests.zone_id', $zoneIds)
            ->whereIn('app_requests.status', self::OPEN_REQUEST_STATUSES)
            ->selectRaw('app_requests.resource_type_id, SUM(app_allocations.quantity_allocated) as total')
            ->groupBy('app_requests.resource_type_id')
            ->pluck('total', 'resource_type_id');

        return collect($requested)
            ->map(fn ($qty, $resourceTypeId) => max(0, $qty - ($allocated[$resourceTypeId] ?? 0)));
    }

    /** @return Collection<int, int> keyed by resource_type_id */
    private function availableStockByResourceType(): Collection
    {
        return DB::table('app_resources')
            ->join('app_depots', 'app_depots.id', '=', 'app_resources.depot_id')
            ->where('app_depots.status', 'OPERATIONAL')
            ->selectRaw('app_resources.resource_type_id, SUM(app_resources.quantity_available - app_resources.quantity_reserved) as total')
            ->groupBy('app_resources.resource_type_id')
            ->pluck('total', 'resource_type_id');
    }
}
