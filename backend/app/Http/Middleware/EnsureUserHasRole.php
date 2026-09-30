<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: Route::middleware('role:logistics,operations')->group(...)
 *
 * A user with `is_system_admin` (the Head of Office) passes every role check,
 * so the Admin has every role's capabilities. `role:system_admin` therefore
 * means "Admin only".
 *
 * Register the alias in bootstrap/app.php (Laravel 11+) or app/Http/Kernel.php ($routeMiddleware).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // The Head of Office (is_system_admin) can do everything any role can.
        $allowed = $user && ($user->is_system_admin || in_array($user->role, $roles, true));

        if (! $allowed) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
