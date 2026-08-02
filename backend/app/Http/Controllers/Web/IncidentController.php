<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the CSR shell for the Incidents page (Pages/Incidents/Index.tsx)
 * — data is fetched client-side via resources/js/api-service/incidents.ts.
 */
class IncidentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Incidents/Index');
    }
}
