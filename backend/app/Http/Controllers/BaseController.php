<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic JSON CRUD base for Api\* controllers — configuration-driven
 * (searchable/filterable/sortable columns) so each concrete controller is a
 * thin binding. Pairs with resources/js/api-service/http.ts's
 * createCrudResource, which expects exactly this envelope/endpoint shape.
 *
 * Route-level `auth:sanctum` + `role:...` middleware (see routes/api.php) is
 * this app's actual authorization mechanism — no per-model Policy classes
 * exist here, so this base does not call $this->authorize().
 */
abstract class BaseController extends Controller
{
    /** Fully qualified model class for this module. Set by child. */
    protected string $model;

    /** Columns allowed for the global "search" param. */
    protected array $searchable = [];

    /** Columns allowed for per-column filters[col]=value. */
    protected array $filterable = [];

    /** Filterable columns matched with `=` instead of `LIKE`. */
    protected array $exactFilters = [];

    /** Columns allowed for sort_by. */
    protected array $sortable = ['id'];

    /** Default sort column. */
    protected string $sortBy = 'id';

    /**
     * Filter keys that live on a related model rather than this model's own
     * table. Map: filter key => dot-path 'relation.column' (may traverse
     * nested relations). Applied via `whereHas` instead of a flat `where`.
     *
     * @var array<string, string>
     */
    protected array $relationFilters = [];

    /**
     * Relations to count when checking if a record is in use before delete.
     * Override in child controllers to enable the in-use guard.
     * e.g. ['resources'] → checks resources_count.
     */
    protected array $inUseRelations = [];

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->get());
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->sendResponse($this->resolveModel($id));
    }

    public function paginationSearch(Request $request): JsonResponse
    {
        $query = $this->newQuery();
        $search = $request->string('search')->toString();

        if ($search !== '' && $this->searchable) {
            $this->applySearchTerms($query, $this->searchable, $search);
        }

        foreach ((array) $request->input('filters', []) as $col => $value) {
            if (is_array($value)) {
                $cleanedValue = array_values(array_filter(
                    $value,
                    fn ($v) => $v !== null && $v !== '',
                ));
                $isEmpty = count($cleanedValue) === 0;
            } else {
                $cleanedValue = is_string($value) ? trim($value) : $value;
                $isEmpty = $cleanedValue === '';
            }

            if ($isEmpty || ! in_array($col, $this->filterable, true)) {
                continue;
            }

            if (array_key_exists($col, $this->relationFilters)) {
                $this->applyRelationFilter($query, $this->relationFilters[$col], $cleanedValue);

                continue;
            }

            if (is_array($cleanedValue)) {
                $query->whereIn($col, $cleanedValue);
            } elseif (in_array($col, $this->exactFilters, true)) {
                $query->where($col, $cleanedValue);
            } else {
                $query->where($col, 'like', "%{$cleanedValue}%");
            }
        }

        $sortBy = $request->string('sort_by', $this->sortBy)->toString();
        $sortDir = $request->string('sort_dir', 'asc')->toString() === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $this->sortable, true)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->paginate(max(1, min($perPage, 100)));

        return $this->sendResponse([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    protected function applyRelationFilter(Builder $query, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $column = array_pop($segments);
        $this->nestedWhereHas($query, $segments, $column, $value);
    }

    /** @param list<string> $relations */
    protected function nestedWhereHas(Builder $query, array $relations, string $column, mixed $value): void
    {
        $relation = array_shift($relations);
        $query->whereHas($relation, function (Builder $q) use ($relations, $column, $value) {
            if (empty($relations)) {
                is_array($value) ? $q->whereIn($column, $value) : $q->where($column, $value);

                return;
            }
            $this->nestedWhereHas($q, $relations, $column, $value);
        });
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules(), $this->validationMessages());
        $validated = $this->beforeSave($validated);
        $model = ($this->model)::create($validated);
        $this->afterCreate($model);

        return $this->sendResponse($model, 'Record created successfully.', 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);
        $validated = $request->validate($this->updateRules($model), $this->validationMessages());
        $validated = $this->beforeSave($validated, $model);
        $model->update($validated);
        $this->afterUpdate($model);

        return $this->sendResponse($model->fresh(), 'Record updated successfully.');
    }

    public function destroy(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        $blocking = $this->inUseBlockers($model);
        if (! empty($blocking)) {
            return $this->sendError('This record is still in use and cannot be deleted.', 422, ['in_use' => $blocking]);
        }

        try {
            $model->delete();
        } catch (QueryException $e) {
            return $this->sendError('This record is still in use and cannot be deleted.', 422, ['in_use' => $blocking]);
        }

        return response()->json(null, 204);
    }

    /** @return array<int, array{label: string, count: int}> */
    protected function inUseBlockers(Model $model): array
    {
        if (empty($this->inUseRelations)) {
            return [];
        }

        $model->loadCount($this->inUseRelations);

        return collect($this->inUseRelations)
            ->filter(fn ($relation) => $model->{"{$relation}_count"} > 0)
            ->map(fn ($relation) => ['label' => ucfirst($relation), 'count' => $model->{"{$relation}_count"}])
            ->values()
            ->all();
    }

    protected function sendResponse(mixed $data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $statusCode);
    }

    protected function sendError(string $message, int $statusCode = 400, ?array $errors = null): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $statusCode);
    }

    /**
     * Splits $search into whitespace-separated terms and requires each term
     * to match at least one of $columns (AND across terms, OR across
     * columns per term). A column may be `relation.column` (e.g.
     * 'resourceType.name') to search across a belongsTo relation via
     * whereHas instead of the model's own table.
     *
     * @param list<string> $columns
     */
    protected function applySearchTerms(Builder $query, array $columns, string $search): void
    {
        $terms = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY);

        $query->where(function (Builder $outer) use ($columns, $terms) {
            foreach ($terms as $term) {
                $outer->where(function (Builder $inner) use ($columns, $term) {
                    foreach ($columns as $column) {
                        if (str_contains($column, '.')) {
                            [$relation, $relatedColumn] = explode('.', $column, 2);
                            $inner->orWhereHas($relation, function (Builder $q) use ($relatedColumn, $term) {
                                $q->where($relatedColumn, 'like', "%{$term}%");
                            });
                        } else {
                            $inner->orWhere($column, 'like', "%{$term}%");
                        }
                    }
                });
            }
        });
    }

    protected function newQuery(): Builder
    {
        return ($this->model)::query();
    }

    protected function resolveModel(int|string $id): Model
    {
        return $this->newQuery()->findOrFail($id);
    }

    /** Override in modules that use store(). */
    protected function storeRules(): array
    {
        return [];
    }

    /** Override in modules that use update(). */
    protected function updateRules(Model $model): array
    {
        return [];
    }

    /** @return array<string, string> */
    protected function validationMessages(): array
    {
        return [];
    }

    protected function afterCreate(Model $model): void {}

    protected function afterUpdate(Model $model): void {}

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        return $validated;
    }
}
