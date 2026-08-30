<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Dedicated unit coverage for EnsureUserHasRole, mirroring
 * EnsureUserIsActiveTest's pattern of constructing the middleware directly
 * and calling handle() with a stub Closure. tests/Feature/RoleGatingTest.php
 * already covers the real route matrix end-to-end; this file isolates edge
 * cases that matrix doesn't exercise directly: a malformed/empty role list,
 * multi-role args where the user matches one of several, a user with no
 * role set, and an unauthenticated request.
 */
class EnsureUserHasRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_a_user_whose_role_matches_the_single_allowed_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $request = Request::create('/reports', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureUserHasRole())->handle($request, fn () => response('passed'), 'admin');

        $this->assertSame('passed', $response->getContent());
    }

    public function test_allows_a_user_matching_one_of_several_comma_separated_roles(): void
    {
        $user = User::factory()->create(['role' => 'coordinator']);
        $request = Request::create('/incidents', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureUserHasRole())->handle(
            $request,
            fn () => response('passed'),
            'admin',
            'coordinator',
            'developer',
        );

        $this->assertSame('passed', $response->getContent());
    }

    public function test_denies_a_user_whose_role_is_not_in_the_allowed_list(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $request = Request::create('/incidents', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        try {
            (new EnsureUserHasRole())->handle($request, fn () => response('should not reach here'), 'admin', 'coordinator');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());

            throw $e;
        }
    }

    public function test_denies_when_no_roles_are_configured_on_the_route(): void
    {
        // A malformed `role:` usage (empty argument list) must fail closed,
        // never silently allow every authenticated user through.
        $user = User::factory()->create(['role' => 'admin']);
        $request = Request::create('/reports', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        (new EnsureUserHasRole())->handle($request, fn () => response('should not reach here'));
    }

    public function test_denies_a_user_with_no_role_set_at_all(): void
    {
        // The `role` column is NOT NULL with a DB default, so an unset role
        // can't be persisted — simulate it in-memory (middleware only reads
        // the attribute, never re-fetches from the database).
        $user = User::factory()->create(['role' => 'viewer']);
        $user->setAttribute('role', null);
        $request = Request::create('/incidents', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        (new EnsureUserHasRole())->handle($request, fn () => response('should not reach here'), 'admin', 'coordinator');
    }

    public function test_denies_an_unauthenticated_request(): void
    {
        $request = Request::create('/incidents', 'GET');
        $request->setUserResolver(fn () => null);

        $this->expectException(HttpException::class);

        (new EnsureUserHasRole())->handle($request, fn () => response('should not reach here'), 'admin', 'coordinator');
    }

    public function test_role_matching_is_strict_and_case_sensitive(): void
    {
        // in_array(..., true) is a strict comparison — 'Admin' must not match 'admin'.
        $user = User::factory()->create(['role' => 'admin']);
        $request = Request::create('/reports', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        (new EnsureUserHasRole())->handle($request, fn () => response('should not reach here'), 'Admin');
    }
}
