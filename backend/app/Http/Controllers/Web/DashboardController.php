<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\Depot;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the home page (Inertia page: Pages/Dashboard.tsx) — a map and
 * incident list only. Routing/navigation, resource inventory, and
 * optimization triggering live under the System Management area instead
 * (see SystemNavigationController, ResourceInventoryController, OptimizationController).
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'incidents' => Disaster::with('zones')->where('status', 'ACTIVE')->get(),
            'depots' => Depot::all(),
        ]);
    }
}
