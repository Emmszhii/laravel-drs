<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Resource;
use App\Models\ResourceLoan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
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

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (! $model) {
            $resource = Resource::with('resourceType')->findOrFail($validated['resource_id']);

            if ($resource->resourceType->is_consumable) {
                throw ValidationException::withMessages([
                    'resource_id' => 'This resource type is consumable and cannot be loaned out.',
                ]);
            }

            $validated['status'] = 'issued';
            $validated['issued_at'] = now();
            $validated['issued_by'] = auth()->id();

            return $validated;
        }

        if (($validated['status'] ?? null) === 'returned' && ! $model->returned_at) {
            $validated['returned_at'] = now();
        }

        return $validated;
    }

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->latest()->get());
    }
}
