<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoutingSetting;
use App\Services\Directions\PremiumDirectionsProvider;
use App\Services\Directions\ProviderQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only settings for the optional paid routing provider tier — see
 * DirectionsRouter for how these settings drive provider selection.
 */
class RoutingSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(RoutingSetting::current()->present());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'active_provider' => ['required', 'in:osrm,premium'],
            'premium_base_url' => ['nullable', 'url'],
            'premium_api_key' => ['nullable', 'string'],
            'premium_monthly_cap' => ['nullable', 'integer', 'min:1'],
        ]);

        $settings = RoutingSetting::current();

        // Don't overwrite a saved key with an empty submission (e.g. the
        // frontend re-submits the form without the masked key visible).
        if (empty($validated['premium_api_key'])) {
            unset($validated['premium_api_key']);
        }

        $settings->update($validated);

        return response()->json($settings->fresh()->present());
    }

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'premium_base_url' => ['required', 'url'],
            'premium_api_key' => ['required', 'string'],
        ]);

        $settings = RoutingSetting::current();

        // Two fixed, nearby Legazpi City points (matches seeded PH data) —
        // cheap enough to be a safe connectivity check for any provider.
        $provider = new PremiumDirectionsProvider($validated['premium_base_url'], $validated['premium_api_key']);

        try {
            $result = $provider->getRoute(13.1449, 123.7515, 13.1391, 123.7438);
            $ok = $result['ok'] ?? false;
        } catch (ProviderQuotaExceededException $e) {
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
