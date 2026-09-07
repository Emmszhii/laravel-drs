<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RoutingSetting;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the system-admin-only Settings → Routing page (Inertia page:
 * Pages/Settings/Routing.tsx) — configure/test/activate the optional
 * premium directions provider. Gated by the `role:system_admin` middleware.
 */
class RoutingSettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Routing', [
            'settings' => RoutingSetting::current()->present(),
        ]);
    }
}
