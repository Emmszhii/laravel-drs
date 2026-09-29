<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\IcsRequestController as ApiIcsRequestController;
use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Depot;
use App\Models\IcsRequest;
use App\Models\ResourceType;
use App\Services\Stock\StockLedger;
use App\Services\Stock\Reservations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ICS 213-RR requests (Pages/IcsRequests/Index.tsx) and the borrowed-equipment
 * logbook (Pages/IcsRequests/Borrowed.tsx) — two subtabs, two routes.
 * Writes go through Api\IcsRequestController.
 */
class IcsRequestController extends Controller
{
    public function index(Request $request, StockLedger $ledger): Response
    {
        $available = $ledger->levels()->map(fn ($l) => [
            'resource_type_id' => $l->resource_type_id,
            'depot_id' => $l->depot_id,
            'available' => (int) $l->quantity - Reservations::heldAt($l->resource_type_id, $l->depot_id),
        ]);

        return Inertia::render('IcsRequests/Index', [
            'requests' => IcsRequest::with('lines.resourceType:id,name,unit,is_consumable', 'requester:id,first_name,last_name', 'history')
                ->latest()->limit(100)->get(),
            'items' => ResourceType::orderBy('name')->get(['id', 'name', 'unit', 'is_consumable']),
            'depots' => Depot::orderBy('name')->get(['id', 'name']),
            'available' => $available,
            'can' => $this->can($request),
        ]);
    }

    public function borrowed(Request $request): Response
    {
        $borrows = Borrow::with('resourceType:id,name', 'history')->latest('issued_at')->limit(200)->get();
        if (ApiIcsRequestController::seesPersonalData($request)) {
            $borrows->each->makeVisible(['borrower_name', 'borrower_contact']);
        }

        return Inertia::render('IcsRequests/Borrowed', ['borrows' => $borrows, 'can' => $this->can($request)]);
    }

    private function can(Request $request): array
    {
        $user = $request->user();

        return [
            'file' => $user->is_system_admin || in_array($user->role, ['logistics', 'ert', 'operations', 'incident_management'], true),
            'decide' => (bool) $user->is_system_admin,
            'release' => $user->is_system_admin || $user->role === 'logistics',
            'userId' => $user->id,
        ];
    }
}
