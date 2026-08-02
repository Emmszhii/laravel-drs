<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Depot map + card grid (Inertia page: Pages/Depots/Index.tsx), admin/developer only.
 * Data is fetched client-side via resources/js/api-service/depots.ts.
 */
class DepotController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Depots/Index');
    }
}
