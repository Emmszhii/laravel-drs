<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserAccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_active_user_can_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_suspend_another_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true, 'is_active' => true]);
        $target = User::factory()->create(['role' => 'administrative', 'is_active' => true]);

        $this->actingAs($admin)
            ->postJson("/api/settings/users/{$target->id}/suspend")
            ->assertOk();

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true, 'is_active' => true]);

        $this->actingAs($admin)
            ->postJson("/api/settings/users/{$admin->id}/suspend")
            ->assertStatus(422);

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_activate_a_suspended_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true, 'is_active' => true]);
        $target = User::factory()->create(['role' => 'administrative', 'is_active' => false]);

        $this->actingAs($admin)
            ->postJson("/api/settings/users/{$target->id}/activate")
            ->assertOk();

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_reset_password_sends_the_reset_mail(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true]);
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->postJson("/api/settings/users/{$target->id}/reset-password")
            ->assertOk();

        Mail::assertSent(PasswordResetMail::class, fn ($mail) => $mail->hasTo($target->email));
    }

    public function test_a_read_only_user_cannot_suspend_users(): void
    {
        $readOnly = User::factory()->create(['role' => 'administrative']);
        $target = User::factory()->create();

        $this->actingAs($readOnly)
            ->postJson("/api/settings/users/{$target->id}/suspend")
            ->assertStatus(403);
    }
}
