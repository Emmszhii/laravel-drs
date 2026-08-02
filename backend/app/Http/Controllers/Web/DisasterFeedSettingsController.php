<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DisasterFeedSetting;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the admin-only Settings → Disaster feed page (Inertia page:
 * Pages/Settings/DisasterFeed.tsx) — configure/enable the USGS/GDACS
 * feeds, polling interval, and auto-resolve. Gated by the `role` middleware.
 */
class DisasterFeedSettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/DisasterFeed', [
            'settings' => DisasterFeedSetting::current()->present(),
        ]);
    }
}
