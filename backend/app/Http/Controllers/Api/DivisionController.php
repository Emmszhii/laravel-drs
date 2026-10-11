<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Division;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Divisions and teams CRUD: backs the Divisions page and the ICS 213-RR
 * form's "Division / team" search (pagination-search, which also matches
 * the parent division's name, so "operations" finds its teams).
 */
class DivisionController extends BaseController
{
    protected string $model = Division::class;

    protected array $searchable = ['name', 'parent.name'];

    protected array $sortable = ['name', 'created_at'];

    protected string $sortBy = 'name';

    protected array $inUseRelations = ['teams'];

    protected function newQuery(): Builder
    {
        return Division::query()->with('parent:id,name');
    }

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('app_divisions', 'name')],
            // One level only: a team sits under a division, never under another team.
            'parent_id' => ['nullable', 'uuid', Rule::exists('app_divisions', 'id')->whereNull('parent_id')],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('app_divisions', 'name')->ignore($model->getKey())],
            'parent_id' => ['nullable', 'uuid', Rule::notIn([$model->getKey()]), Rule::exists('app_divisions', 'id')->whereNull('parent_id'),
                // A division that has teams can't become a team itself.
                fn ($attr, $value, $fail) => $value && $model->teams()->exists() ? $fail('A division with teams cannot be put under another division.') : null],
        ];
    }
}
