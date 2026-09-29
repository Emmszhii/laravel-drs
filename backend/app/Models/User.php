<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\PasswordResetMail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'role', 'is_active', 'is_system_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    /** Computed full name, kept for every existing consumer that reads `->name`. */
    protected $appends = ['name'];

    /**
     * Keeps the legacy `role` string column (read by EnsureUserHasRole) and
     * the Spatie role/permission system (read by the frontend's nav/`can()`
     * checks) in sync on every create/update, regardless of which code path
     * touched `role` — previously only Api\Settings\UserController did this
     * explicitly, so any other creation path (factories, seeders, tinker)
     * left a user passing backend gates with an empty/wrong sidebar.
     * `firstOrCreate` on Role means this never needs RoleSeeder to have run
     * first (e.g. in tests that don't seed roles/permissions).
     */
    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if (! $user->role) {
                return;
            }

            Role::firstOrCreate(['name' => $user->role, 'guard_name' => 'web']);
            $user->syncRoles([$user->role]);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_system_admin' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Shape shared with the frontend as `auth.user` (see HandleInertiaRequests
     * and bootstrap/app.php's 404/429 renderers). Includes Spatie roles/
     * permissions so Hooks/use-permissions.ts's `can`/`hasRole` checks work.
     * The Admin (Head of Office) gets every role and permission.
     */
    public function toInertiaPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'is_system_admin' => $this->is_system_admin,
            'roles' => $this->is_system_admin ? Role::pluck('name')->all() : $this->getRoleNames()->all(),
            'permissions' => $this->is_system_admin ? Permission::pluck('name')->all() : $this->getAllPermissions()->pluck('name')->all(),
        ];
    }

    /**
     * Computed `name` = "first_name last_name", so mail templates,
     * Inertia's shared auth user, and every existing `->name` read keeps
     * working after the column was split into first_name/last_name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    /**
     * Overrides the framework's default plain-text reset notification with
     * the app's own styled Mailable, pointed at the hand-rolled (non-Breeze)
     * reset-password page.
     */
    public function sendPasswordResetNotification($token): void
    {
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);

        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        SmtpSetting::current()->applyToMailer();

        Mail::to($this->email)->send(new PasswordResetMail(
            userName: $this->name,
            userEmail: $this->email,
            resetUrl: $resetUrl,
            expiryMinutes: $expireMinutes,
            requestIp: Request::ip() ?? 'unknown',
            requestTime: now()->toDayDateTimeString(),
        ));
    }
}
