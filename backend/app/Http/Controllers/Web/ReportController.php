<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ReplenishmentRun;
use App\Services\Reports\InventoryBurnRateReportBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The quarterly burn-rate and reorder report (Pages/Report/Index.tsx): an
 * on-screen copy of exactly what the PDF and CSV export print, for any year
 * and quarter range. Every role can read it; the download links are for the
 * roles Api\ReplenishmentController::report lets export.
 */
class ReportController extends Controller
{
    public function index(Request $request, InventoryBurnRateReportBuilder $builder): Response
    {
        // Opens on the latest approved plan's quarter (the one worth reading), else the quarter being planned.
        $latest = ReplenishmentRun::where('status', 'approved')->latest('decided_at')->first(['year', 'quarter']);
        $next = now()->addQuarter();
        $year = min(2100, max(2020, $request->integer('year', $latest->year ?? $next->year)));
        $from = min(4, max(1, $request->integer('from', $latest->quarter ?? $next->quarter)));
        $to = min(4, max($from, $request->integer('to', $from)));

        // ponytail: each quarter re-runs the chain check, like the export does; build it once if reports get slow.
        $reports = collect(range($from, $to))->map(fn (int $q) => $builder->build($year, $q));
        $user = $request->user();

        return Inertia::render('Report/Index', [
            'year' => $year,
            'from' => $from,
            'to' => $to,
            'reports' => $reports,
            'can' => [
                'export' => $user->is_system_admin || in_array($user->role, ['logistics', 'research_planning'], true),
            ],
        ]);
    }
}
