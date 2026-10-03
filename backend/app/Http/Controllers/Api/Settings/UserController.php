<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\BaseController;
use App\Mail\NewAccountMail;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

/**
 * Users CRUD — backs Settings → Users. Legacy `role` string column (read by
 * EnsureUserHasRole) and Spatie roles/permissions stay in sync automatically
 * via User::booted() — see app/Models/User.php.
 */
class UserController extends BaseController
{
    protected string $model = User::class;

    protected array $searchable = ['first_name', 'last_name', 'email'];

    protected array $filterable = ['role', 'is_active'];

    protected array $exactFilters = ['role', 'is_active'];

    protected array $sortable = ['id', 'first_name', 'last_name', 'created_at'];

    protected string $sortBy = 'first_name';

    protected function storeRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:viewer,administrative,logistics,operations,research_planning,admin_training'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'is_system_admin' => ['sometimes', 'boolean'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$model->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'in:viewer,administrative,logistics,operations,research_planning,admin_training'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'is_system_admin' => ['sometimes', 'boolean'],
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
        $token = Password::broker()->createToken($model);
        $setPasswordUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $model->email,
        ], false));

        SmtpSetting::current()->applyToMailer();

        Mail::to($model->email)->send(new NewAccountMail(
            userName: $model->name,
            userEmail: $model->email,
            userRole: $model->role,
            setPasswordUrl: $setPasswordUrl,
            expiryHours: (int) ceil(((int) config('auth.passwords.users.expire', 60)) / 60),
        ));
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

    public function suspend(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        if ($model->id === auth()->id()) {
            return $this->sendError("You can't suspend your own account.", 422);
        }

        $model->update(['is_active' => false]);

        return $this->sendResponse($model->fresh(), 'User suspended.');
    }

    public function activate(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        $model->update(['is_active' => true]);

        return $this->sendResponse($model->fresh(), 'User activated.');
    }

    /**
     * Reuses the same broker + User::sendPasswordResetNotification() ->
     * PasswordResetMail flow already wired for new-account setup.
     */
    public function resetPassword(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        Password::sendResetLink(['email' => $model->email]);

        return $this->sendResponse(null, 'Password reset email sent.');
    }
}
