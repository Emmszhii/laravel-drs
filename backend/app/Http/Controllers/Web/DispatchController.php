<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dispatch tracking board (Inertia page: Pages/Dispatch/Index.tsx). Data is
 * fetched/streamed client-side via api-service/modules/dispatches.ts and the
 * `dispatches` Reverb channel (Hooks/useDispatchTracking.ts).
 */
class DispatchController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dispatch/Index');
    }
}
