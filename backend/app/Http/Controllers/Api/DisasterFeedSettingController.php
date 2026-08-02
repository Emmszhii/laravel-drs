<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\PollDisasterFeedsJob;
use App\Models\DisasterFeedSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only settings for the automated disaster/calamity feed — see
 * App\Jobs\PollDisasterFeedsJob for how these settings drive polling.
 */
class DisasterFeedSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(DisasterFeedSetting::current()->present());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usgs_enabled' => ['required', 'boolean'],
            'usgs_min_magnitude' => ['required', 'numeric', 'min:0', 'max:10'],
            'gdacs_enabled' => ['required', 'boolean'],
            'gdacs_min_alert_level' => ['required', 'in:Green,Orange,Red'],
            'poll_interval_minutes' => ['required', 'in:'.implode(',', DisasterFeedSetting::POLL_INTERVALS)],
            'auto_resolve_enabled' => ['required', 'boolean'],
        ]);

        $settings = DisasterFeedSetting::current();
        $settings->update($validated);

        return response()->json($settings->fresh()->present());
    }

    /**
     * Queues an immediate poll rather than running synchronously — a live
     * call to two external APIs can take too long for a request/response
     * cycle. The frontend re-fetches `show()` after a short delay to see
     * the result (requires the queue worker to be running).
     */
    public function run(): JsonResponse
    {
        PollDisasterFeedsJob::dispatch(manual: true);

        return response()->json(['queued' => true], 202);
    }
}
