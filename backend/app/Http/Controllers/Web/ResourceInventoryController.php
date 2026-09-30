<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the CSR shell for the item catalog (Pages/Resources/Types.tsx);
 * its data is fetched client-side via the resource-types api-service.
 */
class ResourceInventoryController extends Controller
{
    public function types(): Response
    {
        return Inertia::render('Resources/Types');
    }
}
