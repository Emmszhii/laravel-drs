<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reports placeholder (Inertia page: Pages/Reports/Index.tsx), admin/developer only.
 * No Report model exists yet — the design prototype's reports are static demo data.
 */
class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }
}
