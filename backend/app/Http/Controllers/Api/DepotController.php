<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Depot;
use Illuminate\Database\Eloquent\Model;

/**
 * Depots CRUD — backs the admin/developer Depots page.
 */
class DepotController extends BaseController
{
    protected string $model = Depot::class;

    protected array $searchable = ['name', 'address'];

    protected array $filterable = ['status'];

    protected array $exactFilters = ['status'];

    protected array $sortable = ['id', 'name', 'created_at'];

    protected string $sortBy = 'name';

    protected array $inUseRelations = ['resources'];

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:OPERATIONAL,LIMITED CAPACITY,OFFLINE'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:OPERATIONAL,LIMITED CAPACITY,OFFLINE'],
            'lat' => ['sometimes', 'numeric'],
            'lng' => ['sometimes', 'numeric'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
