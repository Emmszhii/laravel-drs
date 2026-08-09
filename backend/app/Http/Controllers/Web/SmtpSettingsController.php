<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the admin-only Settings → SMTP page (Inertia page:
 * Pages/Settings/Smtp.tsx) — configure/test the optional admin-managed
 * outgoing mail server. Gated by the `role:admin,developer` middleware.
 */
class SmtpSettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Smtp', [
            'settings' => SmtpSetting::current()->present(),
        ]);
    }
}
