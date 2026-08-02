<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\RunOptimizationJob;
use App\Models\Disaster;
use Illuminate\Http\RedirectResponse;

/**
 * Lets a coordinator/admin/developer manually re-run the allocation solver
 * for an incident, triggered from the Incidents detail panel
 * (Pages/Incidents/Index.tsx, embedding Pages/Optimization/TriggerSolver.tsx).
 */
class OptimizationController extends Controller
{
    public function trigger(Disaster $disaster): RedirectResponse
    {
        RunOptimizationJob::dispatch($disaster);

        return back()->with('status', "Optimization run queued for {$disaster->name}.");
    }
}
