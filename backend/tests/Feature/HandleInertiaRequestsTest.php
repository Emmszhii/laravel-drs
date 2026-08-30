<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HandleInertiaRequests::share() puts `auth.user` (via User::toInertiaPayload())
 * and `status` (session flash) into every Inertia response's shared props. It's
 * registered as an appended `web` middleware in bootstrap/app.php, so any
 * authenticated Inertia page load carries it — we hit the real dashboard route
 * with the X-Inertia header to read the props Inertia actually serialized.
 */
class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shares_the_authenticated_user_payload_on_an_inertia_request(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();

        $authUser = $response->inertiaProps('auth.user');

        $this->assertSame($user->id, $authUser['id']);
        $this->assertSame($user->email, $authUser['email']);
        $this->assertSame('admin', $authUser['role']);
    }

    public function test_shares_a_null_user_when_the_request_is_unauthenticated(): void
    {
        // Any guest-reachable page works; /public doesn't require auth.
        $response = $this->get('/public');

        $response->assertOk();

        $this->assertNull($response->inertiaProps('auth.user'));
    }

    public function test_shares_the_session_status_flash_as_a_prop(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['status' => 'Password reset successfully.'])
            ->get('/');

        $response->assertOk();

        $this->assertSame('Password reset successfully.', $response->inertiaProps('status'));
    }

    public function test_status_prop_is_null_when_nothing_was_flashed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();

        $this->assertNull($response->inertiaProps('status'));
    }
}
