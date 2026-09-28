<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Allocation;
use App\Models\Resource;
use App\Models\ResourceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Resource-request listing + creation — backs the Dashboard's request table
 * (Pages/Dashboard.tsx) and the per-zone "Request resources" form embedded
 * in Pages/Incidents/Index.tsx.
 */
class RequestController extends BaseController
{
    protected string $model = ResourceRequest::class;

    protected array $searchable = ['zone.name', 'resourceType.name'];

    protected array $filterable = ['status'];

    protected array $exactFilters = ['status'];

    protected array $sortable = ['id', 'quantity_requested', 'created_at'];

    protected string $sortBy = 'created_at';

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->latest()->get());
    }

    protected function newQuery(): Builder
    {
        return ResourceRequest::query()->with(['zone.disaster', 'resourceType']);
    }

    protected function storeRules(): array
    {
        return [
            // Client-supplied id lets the offline-sync outbox (Offline/sync.ts)
            // retry a POST after a partial network failure without risking a
            // duplicate row — see firstOrCreate below.
            'id' => ['sometimes', 'uuid'],
            'zone_id' => ['required', 'uuid', 'exists:app_zones,id'],
            'resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules());
        $id = $validated['id'] ?? (string) Str::uuid();
        unset($validated['id']);

        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'pending';

        // firstOrCreate can't be used here — `id` isn't in $fillable (by
        // design, so it's never mass-assignable from arbitrary input), so
        // its mass-assigned $attributes would be silently dropped and
        // HasUuids would generate a fresh id, defeating the idempotency
        // this client-supplied id exists for.
        $model = ResourceRequest::find($id);
        if (! $model) {
            $model = new ResourceRequest($validated);
            $model->id = $id;
            $model->save();
        }

        return $this->sendResponse($model->load(['zone.disaster', 'resourceType']), 'Record created successfully.', 201);
    }

    /**
     * Department-head sign-off (the office's paper ICS 213-RR form). Also
     * reserves the matching stock at the chosen depot and creates the
     * Allocation row that links this request to that depot/quantity — see
     * DispatchController::updateStatus() for where the reservation is later
     * turned into an actual deduction once the resource is delivered.
     */
    public function approve(Request $httpRequest, ResourceRequest $request): JsonResponse
    {
        $validated = $httpRequest->validate([
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $resource = Resource::where('depot_id', $validated['depot_id'])
                ->where('resource_type_id', $request->resource_type_id)
                ->lockForUpdate()
                ->first();

            $available = $resource ? $resource->quantity_available - $resource->quantity_reserved : 0;
            if ($available < $request->quantity_requested) {
                throw ValidationException::withMessages([
                    'depot_id' => 'Insufficient stock at the selected depot to cover this request.',
                ]);
            }

            $resource->increment('quantity_reserved', $request->quantity_requested);

            Allocation::create([
                'request_id' => $request->id,
                'depot_id' => $validated['depot_id'],
                'quantity_allocated' => $request->quantity_requested,
                'status' => 'reserved',
            ]);

            $request->update([
                'depot_id' => $validated['depot_id'],
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return $this->sendResponse($request->fresh(['zone.disaster', 'resourceType', 'approver', 'depot']), 'Request approved.');
    }

    public function reject(Request $httpRequest, ResourceRequest $request): JsonResponse
    {
        $validated = $httpRequest->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $request->update([
            'approval_status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return $this->sendResponse($request->fresh(['zone.disaster', 'resourceType', 'approver']), 'Request rejected.');
    }
}
