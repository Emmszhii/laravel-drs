<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: Route::middleware('role:logistics,operations')->group(...)
 *
 * The special token `system_admin` is satisfied by `$user->is_system_admin`
 * instead of a literal `role` match — system-configuration access (Settings,
 * user management, routing, etc.) is an independent flag, not tied to any one
 * CDRRMO division, so a route can require e.g. `role:admin_training,system_admin`.
 *
 * Register the alias in bootstrap/app.php (Laravel 11+) or app/Http/Kernel.php ($routeMiddleware).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = $user && (
            in_array($user->role, $roles, true)
            || (in_array('system_admin', $roles, true) && $user->is_system_admin)
        );

        if (! $allowed) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
