<?php

namespace App\Http\Controllers\Api;

use App\Events\DispatchPositionUpdated;
use App\Http\Controllers\BaseController;
use App\Models\Dispatch;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dispatch (resource-in-transit) CRUD + live position/status updates.
 * Backs Pages/Dispatch/Index.tsx and Hooks/useLiveGeolocation.ts.
 */
class DispatchController extends BaseController
{
    protected string $model = Dispatch::class;

    protected array $filterable = ['status', 'disaster_id'];

    protected array $exactFilters = ['status', 'disaster_id'];

    protected array $sortable = ['id', 'status', 'created_at'];

    protected string $sortBy = 'created_at';

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with(['depot', 'driver', 'disaster'])->latest()->get());
    }

    protected function storeRules(): array
    {
        return [
            'disaster_id' => ['required', 'uuid', 'exists:app_disasters,id'],
            'zone_id' => ['nullable', 'uuid', 'exists:app_zones,id'],
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'resource_type_id' => ['nullable', 'uuid', 'exists:app_resource_types,id'],
            'driver_id' => ['required', 'uuid', 'exists:users,id'],
            'allocation_id' => ['nullable', 'uuid', 'exists:app_allocations,id'],
            'vehicle_label' => ['nullable', 'string', 'max:255'],
            'origin_lat' => ['required', 'numeric'],
            'origin_lng' => ['required', 'numeric'],
            'destination_lat' => ['required', 'numeric'],
            'destination_lng' => ['required', 'numeric'],
        ];
    }

    /**
     * GET /api/dispatches/drivers — minimal {id, name} list for the "Log
     * dispatch" driver picker, without exposing the full Settings\Users
     * endpoint (email/role/is_active) to coordinators.
     */
    public function drivers(): JsonResponse
    {
        return $this->sendResponse(
            User::where('is_active', true)->get(['id', 'first_name', 'last_name'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
                ->values()
        );
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (! $model) {
            $validated['status'] = 'pending';
            $validated['created_by'] = auth()->id();
        }

        return $validated;
    }

    /** POST /api/dispatches/{dispatch}/position — driver-reported live GPS position. */
    public function updatePosition(Request $request, Dispatch $dispatch): JsonResponse
    {
        $this->authorizeDriverOrCoordinator($dispatch);

        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $dispatch->current_lat = $validated['lat'];
        $dispatch->current_lng = $validated['lng'];
        $dispatch->last_position_at = now();

        if ($dispatch->status === 'pending') {
            $dispatch->status = 'en_route';
            $dispatch->dispatched_at = now();
        }

        $dispatch->save();

        broadcast(new DispatchPositionUpdated($dispatch));

        return $this->sendResponse($dispatch->fresh());
    }

    /**
     * POST /api/dispatches/{dispatch}/status — mark delivered/cancelled. A
     * transition to 'delivered' on a dispatch linked to an Allocation
     * deducts the reserved stock (see RequestController::approve(), which
     * created the reservation) and marks the request fulfilled — this is
     * the "resource actually left the depot for the field" event, not the
     * approval itself. A dispatch with no linked allocation is a no-op on
     * stock, since it wasn't created through the request/approval flow.
     */
    public function updateStatus(Request $request, Dispatch $dispatch): JsonResponse
    {
        $this->authorizeDriverOrCoordinator($dispatch);

        $validated = $request->validate([
            'status' => ['required', 'in:en_route,delivered,cancelled'],
        ]);

        if ($validated['status'] === 'delivered' && $dispatch->status !== 'delivered') {
            DB::transaction(function () use ($dispatch) {
                $allocation = $dispatch->allocation()->lockForUpdate()->first();

                if ($allocation && $allocation->status !== 'fulfilled') {
                    $resourceRequest = $allocation->request;

                    $resource = Resource::where('depot_id', $allocation->depot_id)
                        ->where('resource_type_id', $resourceRequest->resource_type_id)
                        ->lockForUpdate()
                        ->first();

                    if ($resource) {
                        $resource->decrement('quantity_available', $allocation->quantity_allocated);
                        $resource->decrement('quantity_reserved', $allocation->quantity_allocated);
                    }

                    $allocation->update(['status' => 'fulfilled']);
                    $resourceRequest->update(['status' => 'fulfilled']);
                }

                $dispatch->status = 'delivered';
                $dispatch->delivered_at = now();
                $dispatch->save();
            });

            return $this->sendResponse($dispatch->fresh());
        }

        $dispatch->status = $validated['status'];
        $dispatch->save();

        return $this->sendResponse($dispatch->fresh());
    }

    private function authorizeDriverOrCoordinator(Dispatch $dispatch): void
    {
        $user = auth()->user();
        $isAssignedDriver = $user && $dispatch->driver_id === $user->id;
        $isCoordinatorTier = $user && ($user->is_system_admin || in_array($user->role, ['incident_management', 'operations', 'admin_training'], true));

        abort_unless($isAssignedDriver || $isCoordinatorTier, 403, 'Only the assigned driver or a coordinator can update this dispatch.');
    }
}
