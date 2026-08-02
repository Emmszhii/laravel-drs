<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

/**
 * Stock (depot inventory) CRUD — backs the Resources page's "Stock" subtab.
 */
class ResourceController extends BaseController
{
    protected string $model = Resource::class;

    protected array $searchable = ['resourceType.name', 'depot.name'];

    protected array $filterable = ['depot_id'];

    protected array $exactFilters = ['depot_id'];

    protected array $sortable = ['id', 'quantity_available', 'created_at'];

    protected string $sortBy = 'created_at';

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with(['depot', 'resourceType'])->latest()->get());
    }

    protected function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Resource::query()->with(['depot', 'resourceType']);
    }

    protected function storeRules(): array
    {
        return [
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'quantity_available' => ['required', 'integer', 'min:0'],
            'quantity_reserved' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'depot_id' => ['sometimes', 'uuid', 'exists:app_depots,id'],
            'resource_type_id' => ['sometimes', 'uuid', 'exists:app_resource_types,id'],
            'quantity_available' => ['sometimes', 'integer', 'min:0'],
            'quantity_reserved' => ['sometimes', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ];
    }
}
