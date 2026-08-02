<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_an_active_user_through(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureUserIsActive())->handle($request, fn () => response('passed'));

        $this->assertSame('passed', $response->getContent());
    }

    public function test_redirects_a_suspended_user_to_login_on_a_web_request(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureUserIsActive())->handle($request, fn () => response('should not reach here'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function test_returns_403_for_a_suspended_user_on_an_api_request(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $request = Request::create('/api/depots', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        (new EnsureUserIsActive())->handle($request, fn () => response('should not reach here'));
    }

    public function test_passes_through_a_guest_request_untouched(): void
    {
        $request = Request::create('/login', 'GET');
        $request->setUserResolver(fn () => null);

        $response = (new EnsureUserIsActive())->handle($request, fn () => response('passed'));

        $this->assertSame('passed', $response->getContent());
    }
}
