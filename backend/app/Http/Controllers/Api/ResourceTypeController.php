<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Model;

/**
 * Item catalog CRUD — backs the Items page. unit_cost, ved_class, space_per_unit
 * and is_consumable feed the replenishment model and the ICS request workflow.
 */
class ResourceTypeController extends BaseController
{
    protected string $model = ResourceType::class;

    protected array $searchable = ['name'];

    protected array $filterable = ['unit', 'ved_class'];

    protected array $sortable = ['id', 'name', 'created_at'];

    protected string $sortBy = 'name';

    // An item with stock entries or encoded history can't be deleted (its history would cascade away).
    protected array $inUseRelations = ['stockEntries', 'historyLines'];

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:app_resource_types,name'],
            'unit' => ['required', 'string', 'max:100'],
            ...$this->modelRules(),
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:app_resource_types,name,'.$model->id],
            'unit' => ['sometimes', 'string', 'max:100'],
            ...$this->modelRules(),
        ];
    }

    private function modelRules(): array
    {
        return [
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'ved_class' => ['sometimes', 'in:V,E,D'],
            'space_per_unit' => ['nullable', 'numeric', 'min:0'],
            'is_consumable' => ['sometimes', 'boolean'],
        ];
    }
}
