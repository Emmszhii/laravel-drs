<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Self-service forgot-password flow (Auth\PasswordResetController), backed
 * by Laravel's built-in password broker/token table. Distinct from
 * UserAccountLifecycleTest::test_reset_password_sends_the_reset_mail, which
 * covers the admin-triggered reset (Api\Settings\UserController) — both
 * ultimately route through User::sendPasswordResetNotification(), which
 * sends the app's own PasswordResetMail via Mail::send() rather than
 * Laravel's default notification.
 */
class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_renders_the_inertia_page(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_send_reset_link_sends_the_password_reset_mail_for_an_existing_user(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'If that email is registered, a reset link is on its way.');

        Mail::assertSent(PasswordResetMail::class, fn ($mail) => $mail->hasTo($user->email)
            && $mail->userEmail === $user->email);
    }

    /**
     * The controller always reports success, regardless of whether the
     * email matches an account, to avoid leaking which addresses have
     * accounts — so no mail is sent, but the same generic status is flashed.
     */
    public function test_send_reset_link_reports_success_without_sending_mail_for_an_unknown_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'If that email is registered, a reset link is on its way.');

        Mail::assertNothingSent();
    }

    public function test_send_reset_link_requires_a_valid_email(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'not-an-email']);

        $response->assertSessionHasErrors('email');
    }

    public function test_create_renders_the_reset_password_page_with_token_and_email(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get("/password/reset/{$token}?email={$user->email}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Auth/ResetPassword')
            ->where('token', $token)
            ->where('email', $user->email));
    }

    public function test_store_changes_the_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $token = Password::createToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'Password set — you can now log in.');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('new-password-123', $fresh->password));
        $this->assertFalse(Hash::check('old-password', $fresh->password));
    }

    public function test_store_does_not_change_the_password_with_an_invalid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $response = $this->post('/password/reset', [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('email');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('old-password', $fresh->password));
        $this->assertFalse(Hash::check('new-password-123', $fresh->password));
    }

    public function test_store_requires_password_confirmation_to_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $token = Password::createToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'does-not-match',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
