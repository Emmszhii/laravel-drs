<?php

namespace App\Services\Directions;

use App\Models\RoutingSetting;

/**
 * Single entry point for getting a route: decides whether to use the free
 * self-hosted OSRM engine or an admin-configured premium provider, tracks
 * monthly usage against a configured cap, and automatically falls back to
 * OSRM if the cap is hit or the premium provider errors out — so directions
 * never hard-fail just because a paid plan ran out.
 */
class DirectionsRouter
{
    public function __construct(protected OsrmDirectionsProvider $osrm)
    {
    }

    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $settings = $this->resetUsagePeriodIfNeeded(RoutingSetting::current());

        if ($settings->active_provider !== 'premium' || ! $settings->premium_api_key) {
            return $this->osrm->getRoute($fromLat, $fromLng, $toLat, $toLng) + ['provider' => 'osrm'];
        }

        if ($settings->premium_monthly_cap !== null && $settings->usage_count >= $settings->premium_monthly_cap) {
            $this->markFallback($settings, 'cap_exceeded');

            return $this->osrm->getRoute($fromLat, $fromLng, $toLat, $toLng) + [
                'provider' => 'osrm',
                'fallback_reason' => 'cap_exceeded',
            ];
        }

        $settings->increment('usage_count');

        try {
            $premium = new PremiumDirectionsProvider($settings->premium_base_url, $settings->premium_api_key);

            return $premium->getRoute($fromLat, $fromLng, $toLat, $toLng) + ['provider' => 'premium'];
        } catch (ProviderQuotaExceededException $e) {
            $this->markFallback($settings, 'provider_error');

            return $this->osrm->getRoute($fromLat, $fromLng, $toLat, $toLng) + [
                'provider' => 'osrm',
                'fallback_reason' => 'provider_error',
            ];
        }
    }

    protected function resetUsagePeriodIfNeeded(RoutingSetting $settings): RoutingSetting
    {
        $currentPeriod = now()->format('Y-m');

        if ($settings->usage_period !== $currentPeriod) {
            $settings->update([
                'usage_period' => $currentPeriod,
                'usage_count' => 0,
                'fallback_triggered_at' => null,
                'fallback_reason' => null,
            ]);
        }

        return $settings;
    }

    protected function markFallback(RoutingSetting $settings, string $reason): void
    {
        if ($settings->fallback_triggered_at === null) {
            $settings->update([
                'fallback_triggered_at' => now(),
                'fallback_reason' => $reason,
            ]);
        }
    }
}
