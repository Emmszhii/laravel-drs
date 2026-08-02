<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes the gap where a user suspended (is_active=false) mid-session keeps
 * full access until their session naturally expires — login itself is
 * already blocked in AuthenticatedSessionController::store().
 *
 * Usage: Route::middleware(['auth', 'active'])->group(...) — must run after
 * `auth`/`auth:sanctum` so $request->user() is resolved.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                abort(403, 'Your account has been suspended. Contact an administrator.');
            }

            return redirect('/login')->withErrors([
                'email' => 'Your account has been suspended. Contact an administrator.',
            ]);
        }

        return $next($request);
    }
}
