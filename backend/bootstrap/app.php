<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->statefulApi();

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );
        // Capture real application errors to the System Log (app_loggers) with
        // actor + stack trace. Skip HTTP-status exceptions (404/429/403/etc.)
        // and validation failures — those are expected control flow, not bugs,
        // and would otherwise flood the log with every broken link or bad form.
        $exceptions->report(function (\Throwable $e) {
            if (
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                || $e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
            ) {
                return;
            }
            \App\Support\ActivityLogger::logError($e);
        });
        // 429 - Too Many Requests
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            /** @disregard P1013 */ // this disregard the error below but it works
            $user = auth()?->user() ?? [];
            return inertia('pages-errors/429', [
                'auth' => ['user' => $user ? $user->toInertiaPayload() : null],
                'title' => 'Too Many Requests',
                'message' => 'You have exceeded your allowed rate limit. Please try again later.'
            ])->toResponse(request())->setStatusCode(429);
        });
        // 404 - Page Not Found
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            /** @disregard P1013 */
            $user = auth()?->user() ?? null;
            return inertia('pages-errors/404', [
                'auth' => ['user' => $user ? $user->toInertiaPayload() : null],
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist or has been moved.'
            ])->toResponse(request())->setStatusCode(404);
        });
    })->create();

// Single .env lives at the repo root (shared with docker-compose's `env_file:`)
// instead of backend/.env, so local `php artisan` commands read/write the same
// file Docker uses.
$app->useEnvironmentPath(dirname(__DIR__, 2));

return $app;
