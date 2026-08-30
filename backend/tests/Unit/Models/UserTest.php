<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2026-01-01 00:00:00',
            'is_active' => 1,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
        // password should be hashed, not stored plainly.
        $this->assertNotSame('password', $user->password);
    }

    public function test_name_attribute_combines_first_and_last_name(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $this->assertSame('Juan Dela Cruz', $user->name);
    }

    public function test_is_active_returns_the_is_active_flag(): void
    {
        $activeUser = User::factory()->create(['is_active' => true]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    public function test_saving_a_user_syncs_the_legacy_role_column_to_spatie_roles(): void
    {
        $user = User::factory()->create(['role' => 'coordinator']);

        $this->assertTrue($user->hasRole('coordinator'));
    }

    public function test_saving_a_user_without_a_role_does_not_error_or_assign_roles(): void
    {
        $user = User::factory()->create(['role' => '']);

        $this->assertCount(0, $user->getRoleNames());
    }

    public function test_to_inertia_payload_shape(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'role' => 'admin',
        ]);

        $payload = $user->toInertiaPayload();

        $this->assertSame($user->id, $payload['id']);
        $this->assertSame('Ana Reyes', $payload['name']);
        $this->assertSame($user->email, $payload['email']);
        $this->assertSame('admin', $payload['role']);
        $this->assertTrue($payload['is_active']);
        $this->assertContains('admin', $payload['roles']);
        $this->assertIsArray($payload['permissions']);
    }
}
