<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Admin-only settings for the optional admin-managed outgoing SMTP server
 * — see SmtpSetting::applyToMailer() for how these settings (or the lack
 * of them) drive mail delivery.
 */
class SmtpSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(SmtpSetting::current()->present());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = SmtpSetting::current();

        // Don't overwrite a saved password with an empty submission (e.g.
        // the frontend re-submits the form without retyping it).
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $settings->update($validated);

        return response()->json($settings->fresh()->present());
    }

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'to' => ['nullable', 'email'],
        ]);

        $settings = SmtpSetting::current();

        // Password is optional in the request so the admin doesn't have to
        // retype an already-saved one just to test the rest of the form.
        $password = $validated['password'] ?? $settings->password;

        $to = $validated['to'] ?? $request->user()?->email;

        config([
            'mail.mailers.smtp.host' => $validated['host'],
            'mail.mailers.smtp.port' => $validated['port'],
            'mail.mailers.smtp.username' => $validated['username'] ?? null,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => ($validated['encryption'] ?? null) === 'ssl' ? 'smtps' : null,
            'mail.default' => 'smtp',
            'mail.from.address' => $validated['from_address'],
            'mail.from.name' => $validated['from_name'] ?? config('mail.from.name'),
        ]);

        try {
            Mail::raw('This is a test email from DisasterResource+ to verify your SMTP settings.', function ($message) use ($to) {
                $message->to($to)->subject('DisasterResource+ SMTP test');
            });
            $ok = true;
        } catch (\Throwable $e) {
            $ok = false;
        }

        $settings->update([
            'last_test_status' => $ok ? 'ok' : 'failed',
            'last_test_at' => now(),
        ]);

        return response()->json([
            'ok' => $ok,
            'tested_at' => $settings->last_test_at,
        ]);
    }
}
