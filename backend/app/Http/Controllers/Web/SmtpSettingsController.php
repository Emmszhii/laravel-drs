<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the system-admin-only Settings → SMTP page (Inertia page:
 * Pages/Settings/Smtp.tsx) — configure/test the optional admin-managed
 * outgoing mail server. Gated by the `role:system_admin` middleware.
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
