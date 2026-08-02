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
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    /** Computed full name, kept for every existing consumer that reads `->name`. */
    protected $appends = ['name'];

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
        ];
    }

    public function isActive(): bool
    {
        return $this->is_active;
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
