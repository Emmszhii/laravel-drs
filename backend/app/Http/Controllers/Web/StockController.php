<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\ResourceType;
use App\Models\StockConflict;
use App\Services\Stock\StockLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Stock records page (Pages/Stock/Index.tsx). Entries are recorded into the
 * device's offline outbox first and synced to Api\StockController::sync, so
 * the page keeps working through outages (it's cached by the service worker).
 */
class StockController extends Controller
{
    public function index(Request $request, StockLedger $ledger): Response
    {
        $user = $request->user();
        $isKeeper = $user->is_system_admin || $user->role === 'logistics';

        return Inertia::render('Stock/Index', [
            'items' => ResourceType::orderBy('name')->get(['id', 'name', 'unit']),
            'depots' => Depot::orderBy('name')->get(['id', 'name']),
            'levels' => $ledger->levels(),
            'conflicts' => $isKeeper
                ? StockConflict::with('entry:id,seq,resource_type_id,depot_id,quantity,reason,device,created_at_device')
                    ->where('status', 'open')->latest()->get()
                : [],
            'latestFingerprint' => $ledger->latestFingerprint(),
            'can' => [
                'record' => $isKeeper,
                'resolve' => $isKeeper, // the Admin passes every role check
            ],
        ]);
    }
}
