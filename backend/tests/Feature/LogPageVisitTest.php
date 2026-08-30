<?php

namespace Tests\Feature;

use App\Http\Middleware\LogPageVisit;
use App\Models\AppLogger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

/**
 * LogPageVisit writes a 'visit' row to app_loggers (via ActivityLogger) for
 * authenticated GET requests that resolve to an Inertia page. It isn't
 * currently attached to any route in routes/web.php or bootstrap/app.php
 * (confirmed via repo-wide grep — only the class definition itself
 * references the name), so there's no real route to drive it through the
 * full HTTP kernel. Instead we exercise it directly against handle(),
 * the same pattern tests/Unit/EnsureUserIsActiveTest.php uses for
 * EnsureUserHasRole/EnsureUserIsActive.
 */
class LogPageVisitTest extends TestCase
{
    use RefreshDatabase;

    private function routeNamed(?string $name): Route
    {
        $route = new Route('GET', '/x', []);
        if ($name !== null) {
            $route->name($name);
        }

        return $route;
    }

    /**
     * User::factory()->create() itself fires AppLoggerObserver's 'create'
     * hook, writing an app_loggers row unrelated to the middleware under
     * test. Assert on 'visit' rows specifically rather than the table total.
     */
    private function visitLogCount(): int
    {
        return AppLogger::where('action', 'visit')->count();
    }

    public function test_logs_a_visit_for_an_authenticated_inertia_get_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/incidents', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('incidents.index'));

        $response = (new LogPageVisit())->handle($request, fn () => response()->json(['component' => 'Incidents']));

        $response->headers->set('X-Inertia', 'true');

        $this->assertSame(1, $this->visitLogCount());
        $log = AppLogger::where('action', 'visit')->first();
        $this->assertSame($user->id, $log->actor_id);
        $this->assertSame('incidents.index', $log->changes['route']);
    }

    public function test_logs_a_visit_for_the_initial_full_html_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('dashboard'));

        (new LogPageVisit())->handle(
            $request,
            fn () => response('<html></html>')->header('Content-Type', 'text/html; charset=UTF-8'),
        );

        $this->assertSame(1, $this->visitLogCount());
    }

    public function test_does_not_log_for_a_guest_request(): void
    {
        $request = Request::create('/public', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(fn () => $this->routeNamed('public'));

        (new LogPageVisit())->handle($request, fn () => response()->json(['component' => 'Public']));

        $this->assertSame(0, $this->visitLogCount());
    }

    public function test_does_not_log_for_a_non_get_request(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/incidents', 'POST');
        $request->headers->set('X-Inertia', 'true');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('incidents.store'));

        (new LogPageVisit())->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(0, $this->visitLogCount());
    }

    public function test_does_not_log_a_data_endpoint_matching_a_skip_segment(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/api/incidents/pagination-search', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('incidents.pagination-search'));

        (new LogPageVisit())->handle($request, fn () => response()->json(['data' => []]));

        $this->assertSame(0, $this->visitLogCount());
    }

    public function test_does_not_log_visits_to_the_system_log_page_itself(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/system-log', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('system-log.index'));

        (new LogPageVisit())->handle($request, fn () => response()->json(['component' => 'SystemLog']));

        $this->assertSame(0, $this->visitLogCount());
    }

    public function test_does_not_log_a_plain_json_api_response_without_inertia_header(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/api/incidents/1', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => $this->routeNamed('incidents.show'));

        (new LogPageVisit())->handle($request, fn () => response()->json(['data' => []]));

        $this->assertSame(0, $this->visitLogCount());
    }
}
