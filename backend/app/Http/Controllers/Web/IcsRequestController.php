<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\IcsRequestController as ApiIcsRequestController;
use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Depot;
use App\Models\Division;
use App\Models\IcsRequest;
use App\Models\Kit;
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
    /** Status history with who made each change and their position (the form's signature lines, digitally). */
    private const HISTORY = 'history.user:id,first_name,last_name,position';

    /** Suggestions for the borrower-office inputs (which also take any other office, e.g. "CGSO"): the Divisions page's entries. */
    private static function divisions(): array
    {
        return Division::with('parent:id,name')->get()->pluck('label')->sort()->values()->all();
    }

    public function index(Request $request, StockLedger $ledger): Response
    {
        $available = $ledger->levels()->map(fn ($l) => [
            'resource_type_id' => $l->resource_type_id,
            'depot_id' => $l->depot_id,
            'available' => (int) $l->quantity - Reservations::heldAt($l->resource_type_id, $l->depot_id),
        ]);

        return Inertia::render('IcsRequests/Index', [
            'requests' => IcsRequest::with('lines.resourceType:id,name,unit,category,is_consumable', 'requester:id,first_name,last_name', self::HISTORY)
                ->latest()->limit(100)->get(),
            'items' => ResourceType::orderBy('name')->get(['id', 'name', 'unit', 'category', 'is_consumable']),
            'kits' => Kit::with('lines:id,kit_id,resource_type_id,quantity')->orderBy('name')->get(['id', 'name']),
            'depots' => Depot::orderBy('name')->get(['id', 'name']),
            'available' => $available,
            'divisions' => self::divisions(),
            'can' => $this->can($request),
        ]);
    }

    public function borrowed(Request $request): Response
    {
        $borrows = Borrow::with('resourceType:id,name', self::HISTORY)->latest('issued_at')->limit(200)->get();
        if (ApiIcsRequestController::seesPersonalData($request)) {
            $borrows->each->makeVisible(['borrower_name', 'borrower_contact']);
        }

        return Inertia::render('IcsRequests/Borrowed', [
            'borrows' => $borrows,
            'equipment' => ResourceType::where('is_consumable', false)->orderBy('name')->get(['id', 'name', 'unit']),
            'depots' => Depot::orderBy('name')->get(['id', 'name']),
            'divisions' => self::divisions(),
            'can' => $this->can($request),
        ]);
    }

    private function can(Request $request): array
    {
        $user = $request->user();

        return [
            'file' => $user->is_system_admin || in_array($user->role, ['logistics', 'operations'], true),
            'decide' => (bool) $user->is_system_admin,
            'release' => $user->is_system_admin || $user->role === 'logistics',
            'userId' => $user->id,
        ];
    }
}
