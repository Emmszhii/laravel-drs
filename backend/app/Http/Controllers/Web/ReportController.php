<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reports page (Inertia page: Pages/Reports/Index.tsx), admin/developer only.
 * Data is fetched client-side via resources/js/api-service/reports.ts.
 */
class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }
}
