<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Incidents list + detail (Inertia page: Pages/Incidents/Index.tsx),
 * admin/developer only. Detail panel embeds TriggerSolver for re-running
 * the allocation solver — see OptimizationController::trigger().
 */
class IncidentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Incidents/Index', [
            'incidents' => Disaster::with('zones')->latest()->get(),
        ]);
    }
}
