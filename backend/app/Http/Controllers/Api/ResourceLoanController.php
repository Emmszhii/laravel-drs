<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Resource;
use App\Models\ResourceLoan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Borrowed-item lifecycle (issued/returned/for_repair/condemned/surrendered) —
 * backs the Resources → Borrowed Items tab. Only resources whose type is
 * `is_consumable = false` may be loaned out.
 */
class ResourceLoanController extends BaseController
{
    protected string $model = ResourceLoan::class;

    protected array $filterable = ['status', 'resource_id'];

    protected array $exactFilters = ['status', 'resource_id'];

    protected array $sortable = ['id', 'issued_at', 'due_at', 'created_at'];

    protected string $sortBy = 'created_at';

    protected function newQuery(): Builder
    {
        return ResourceLoan::query()->with(['resource.depot', 'resource.resourceType', 'issuedBy']);
    }

    protected function storeRules(): array
    {
        return [
            'resource_id' => ['required', 'uuid', 'exists:app_resources,id'],
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrower_contact' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'status' => ['sometimes', 'in:issued,returned,for_repair,condemned,surrendered'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->latest()->get());
    }

    /**
     * Borrowed items are "reserved," not deducted from quantity_available,
     * since they come back — see quantity_reserved release in update()
     * below on return. Validated + locked + incremented in one transaction
     * so two concurrent issues can't both pass the availability check.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules());

        $typeResource = Resource::with('resourceType')->findOrFail($validated['resource_id']);
        if ($typeResource->resourceType->is_consumable) {
            throw ValidationException::withMessages([
                'resource_id' => 'This resource type is consumable and cannot be loaned out.',
            ]);
        }

        $loan = DB::transaction(function () use ($validated) {
            $resource = Resource::whereKey($validated['resource_id'])->lockForUpdate()->first();

            if ($resource->quantity_available - $resource->quantity_reserved < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock available to issue this quantity.',
                ]);
            }

            $resource->increment('quantity_reserved', $validated['quantity']);

            return ResourceLoan::create($validated + [
                'status' => 'issued',
                'issued_at' => now(),
                'issued_by' => auth()->id(),
            ]);
        });

        return $this->sendResponse($loan->load(['resource.depot', 'resource.resourceType', 'issuedBy']), 'Record created successfully.', 201);
    }

    /** On transition to 'returned', releases the reservation taken in store() above. */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $loan = $this->resolveModel($id);
        $validated = $request->validate($this->updateRules($loan));

        DB::transaction(function () use (&$validated, $loan) {
            if (($validated['status'] ?? null) === 'returned' && $loan->status !== 'returned') {
                $resource = Resource::whereKey($loan->resource_id)->lockForUpdate()->first();
                $resource?->decrement('quantity_reserved', $loan->quantity);
                $validated['returned_at'] = now();
            }

            $loan->update($validated);
        });

        return $this->sendResponse($loan->fresh(['resource.depot', 'resource.resourceType', 'issuedBy']), 'Record updated successfully.');
    }
}
