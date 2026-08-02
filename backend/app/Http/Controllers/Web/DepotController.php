<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Depot map + card grid (Inertia page: Pages/Depots/Index.tsx), admin/developer only.
 */
class DepotController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Depots/Index', [
            'depots' => Depot::all(),
        ]);
    }
}
