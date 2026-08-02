<?php

namespace App\Jobs;

use App\Events\DisasterAlertEvent;
use App\Models\Disaster;
use App\Models\DisasterFeedSetting;
use App\Services\DisasterFeed\DepotCapacityChecker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Auto-resolves ACTIVE/MONITORING incidents (feed-sourced or manually
 * logged — see Settings → Disaster feed for the toggle) once they've had
 * no open resource requests for DISASTER_FEED_AUTO_RESOLVE_MAX_DAYS and
 * depot stock covers whatever demand remains.
 */
class AutoResolveIncidentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    private const OPEN_REQUEST_STATUSES = ['pending', 'unmet', 'enroute'];

    public function handle(DepotCapacityChecker $capacityChecker): void
    {
        $settings = DisasterFeedSetting::current();

        if (! $settings->auto_resolve_enabled) {
            return;
        }

        $maxDays = (int) config('services.disaster_feed.auto_resolve_max_days', 3);
        $cutoff = now()->subDays($maxDays);

        $candidates = Disaster::query()
            ->whereIn('status', ['ACTIVE', 'MONITORING'])
            ->where('created_at', '<=', $cutoff)
            // (a) nothing still open right now
            ->whereDoesntHave('zones.requests', fn ($q) => $q->whereIn('status', self::OPEN_REQUEST_STATUSES))
            // (b) no request activity at all inside the window
            ->whereDoesntHave('zones.requests', fn ($q) => $q->where('created_at', '>', $cutoff))
            ->get();

        foreach ($candidates as $disaster) {
            // (c) any remaining demand (should be none after (a)/(b), but this
            // is the explicit, testable "resources are capable" guard).
            if (! $capacityChecker->canCoverOutstanding($disaster)) {
                continue;
            }

            $disaster->update(['status' => 'RESOLVED']);
            broadcast(new DisasterAlertEvent($disaster));
        }
    }
}
