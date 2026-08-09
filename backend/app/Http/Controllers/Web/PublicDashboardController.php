<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Disaster;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Unauthenticated read-only view (the "Public users" RBAC tier) — active
 * incidents and depot locations only, no stock quantities or contact
 * details. Mirrors the plain `/about` route: no `auth`/`role` middleware.
 */
class PublicDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Dashboard', [
            'incidents' => Disaster::where('status', 'ACTIVE')
                ->get(['id', 'name', 'type', 'signal', 'zone', 'lat', 'lng', 'status']),
            'depots' => Depot::all()->map(fn (Depot $depot) => [
                'id' => $depot->id,
                'name' => $depot->name,
                'lat' => $depot->lat,
                'lng' => $depot->lng,
            ]),
        ]);
    }
}
