<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\ResourceType;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource types CRUD — backs the Resources page's "Resource types" subtab.
 */
class ResourceTypeController extends BaseController
{
    protected string $model = ResourceType::class;

    protected array $searchable = ['name'];

    protected array $filterable = ['unit'];

    protected array $sortable = ['id', 'name', 'created_at'];

    protected string $sortBy = 'name';

    protected array $inUseRelations = ['resources', 'requests'];

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:app_resource_types,name'],
            'unit' => ['required', 'string', 'max:100'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:app_resource_types,name,'.$model->id],
            'unit' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
