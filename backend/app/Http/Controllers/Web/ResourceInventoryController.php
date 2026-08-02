<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the CSR shell for the Resources page (Pages/Resources/Index.tsx)
 * — data is fetched client-side via the resource-types/resources api-service.
 */
class ResourceInventoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Resources/Index');
    }

    public function types(): Response
    {
        return Inertia::render('Resources/Types');
    }
}
