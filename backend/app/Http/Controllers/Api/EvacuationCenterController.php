<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\EvacuationCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

/** Evacuation center CRUD — backs Pages/Evacuation/Index.tsx. */
class EvacuationCenterController extends BaseController
{
    protected string $model = EvacuationCenter::class;

    protected array $searchable = ['name', 'address'];

    protected array $filterable = ['status', 'disaster_id'];

    protected array $exactFilters = ['status', 'disaster_id'];

    protected array $sortable = ['id', 'name', 'created_at'];

    protected string $sortBy = 'name';

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with('disaster')->latest()->get());
    }

    protected function newQuery(): Builder
    {
        return EvacuationCenter::query()->with('disaster');
    }

    protected function storeRules(): array
    {
        return [
            'disaster_id' => ['required', 'uuid', 'exists:app_disasters,id'],
            'zone_id' => ['nullable', 'uuid', 'exists:app_zones,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'families_served' => ['nullable', 'integer', 'min:0'],
            'individuals_served' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:OPEN,CLOSED'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['sometimes', 'numeric'],
            'lng' => ['sometimes', 'numeric'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'families_served' => ['nullable', 'integer', 'min:0'],
            'individuals_served' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:OPEN,CLOSED'],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (! $model) {
            $validated['status'] = $validated['status'] ?? 'OPEN';
        }

        return $validated;
    }
}
