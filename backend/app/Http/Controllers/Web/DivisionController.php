<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Divisions and teams (Inertia page: Pages/Divisions/Index.tsx): every role reads,
 * Administration and Training edits. Data is fetched client-side via api-service/modules/divisions.ts.
 */
class DivisionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Divisions/Index');
    }
}
