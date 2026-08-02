<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\Depot;
use App\Models\ResourceRequest;
use App\Models\ResourceType;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the home page (Inertia page: Pages/Dashboard.tsx) — EOC-style
 * overview: incident rail + map, aggregate stats, and per-type utilization,
 * all computed from real seeded data (ResourceRequest/Resource rows).
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'incidents' => Disaster::with('zones')->where('status', 'ACTIVE')->get(),
            'depots' => Depot::all(),
            'stats' => $this->stats(),
            'utilization' => $this->utilization(),
        ]);
    }

    private function stats(): array
    {
        $total = ResourceRequest::count();
        $unmet = ResourceRequest::where('status', 'unmet')->count();
        $deployed = ResourceRequest::where('status', '!=', 'unmet')->sum('quantity_requested');

        return [
            'unmet_pct' => $total > 0 ? round(($unmet / $total) * 100) : 0,
            'deployed' => (int) $deployed,
        ];
    }

    private function utilization(): array
    {
        return ResourceType::withSum('resources', 'quantity_available')
            ->withSum('resources', 'quantity_reserved')
            ->get()
            ->map(function (ResourceType $type) {
                $available = (int) ($type->resources_sum_quantity_available ?? 0);
                $reserved = (int) ($type->resources_sum_quantity_reserved ?? 0);
                $totalStock = $available + $reserved;

                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'unit' => $type->unit,
                    'pct' => $totalStock > 0 ? round(($available / $totalStock) * 100) : 0,
                ];
            })
            ->values()
            ->all();
    }
}
