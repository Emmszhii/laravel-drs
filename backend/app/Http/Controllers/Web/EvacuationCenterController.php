<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Evacuation centers page (Inertia page: Pages/Evacuation/Index.tsx). Data
 * is fetched client-side via api-service/modules/evacuationCenters.ts.
 */
class EvacuationCenterController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Evacuation/Index');
    }
}
