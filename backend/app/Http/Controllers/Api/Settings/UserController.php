<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Users CRUD — backs Settings → Users. Keeps the legacy `role` string
 * column (read by EnsureUserHasRole) and the Spatie role/permission system
 * (see RoleSeeder) in sync, same dual-system approach as UserSeeder.
 */
class UserController extends BaseController
{
    protected string $model = User::class;

    protected array $searchable = ['name', 'email'];

    protected array $filterable = ['role', 'is_active'];

    protected array $exactFilters = ['role', 'is_active'];

    protected array $sortable = ['id', 'name', 'created_at'];

    protected string $sortBy = 'name';

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:developer,admin,coordinator,viewer,respondent'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$model->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'in:developer,admin,coordinator,viewer,respondent'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (array_key_exists('password', $validated)) {
            $validated['password'] = $validated['password'] ? Hash::make($validated['password']) : $model?->password;
        }

        return $validated;
    }

    protected function afterCreate(Model $model): void
    {
        $model->syncRoles([$model->role]);
    }

    protected function afterUpdate(Model $model): void
    {
        if ($model->wasChanged('role')) {
            $model->syncRoles([$model->role]);
        }
    }

    public function destroy(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        if ($model->id === auth()->id()) {
            return $this->sendError("You can't delete your own account.", 422);
        }

        $model->delete();

        return response()->json(null, 204);
    }
}
