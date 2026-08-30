<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Covers PUT /api/profile/password (Api\ProfileController::updatePassword),
 * which sits inside the plain auth:sanctum + active middleware group in
 * routes/api.php (no role:... restriction) and uses BaseController's
 * sendResponse/sendError envelope.
 */
class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_own_password(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Password updated successfully.',
            'data' => null,
        ]);

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('new-password-456', $fresh->password));
        $this->assertFalse(Hash::check('old-password-123', $fresh->password));
    }

    public function test_wrong_current_password_is_rejected_and_password_is_not_changed(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'totally-wrong-password',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
        $response->assertJsonFragment([
            'current_password' => ['The current password is incorrect.'],
        ]);

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('old-password-123', $fresh->password));
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'does-not-match',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('old-password-123', $fresh->password));
    }

    public function test_new_password_shorter_than_minimum_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('old-password-123', $fresh->password));
    }

    public function test_missing_fields_are_rejected(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($user)->putJson('/api/profile/password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password', 'password']);
    }

    public function test_unauthenticated_user_cannot_update_password(): void
    {
        $this->putJson('/api/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertStatus(401);
    }

    public function test_suspended_user_cannot_update_password(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'is_active' => false,
            'password' => Hash::make('old-password-123'),
        ]);

        $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertStatus(403);
    }
}
