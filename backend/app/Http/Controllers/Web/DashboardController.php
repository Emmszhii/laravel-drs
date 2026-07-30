<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\Depot;
use App\Models\ResourceRequest;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the coordinator-facing EOC dashboard (Inertia page: Pages/Dashboard.tsx).
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'incidents' => Disaster::with('zones')->where('status', 'ACTIVE')->get(),
            'depots' => Depot::all(),
            'requests' => ResourceRequest::with('zone')->latest()->limit(50)->get(),
        ]);
    }
}
