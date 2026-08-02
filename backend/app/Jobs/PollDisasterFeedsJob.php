<?php

namespace App\Jobs;

use App\Models\DisasterFeedSetting;
use App\Services\DisasterFeed\DisasterFeedIngestor;
use App\Services\DisasterFeed\GdacsFeedClient;
use App\Services\DisasterFeed\UsgsEarthquakeFeedClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Polls the enabled disaster feeds (USGS/GDACS) and ingests results into
 * app_disasters. Dispatched either by the scheduler (routes/console.php,
 * gated on DisasterFeedSetting::dueToPoll()) or manually via the
 * Settings → Disaster feed "Run now" button ($manual = true skips the
 * due-to-poll re-check).
 */
class PollDisasterFeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(public bool $manual = false)
    {
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('disaster-feed-poll'))->expireAfter(300)->dontRelease()];
    }

    public function handle(
        UsgsEarthquakeFeedClient $usgs,
        GdacsFeedClient $gdacs,
        DisasterFeedIngestor $ingestor,
    ): void {
        $settings = DisasterFeedSetting::current();

        if (! $this->manual && ! $settings->dueToPoll()) {
            return;
        }

        $incidents = [];
        $errors = [];

        foreach ([$usgs, $gdacs] as $client) {
            $result = $client->fetchRecent($settings);

            if (! $result['ok']) {
                $errors[] = "{$client->key()}: {$result['error']}";
            }

            $incidents = array_merge($incidents, $result['incidents']);
        }

        $counts = $ingestor->ingest($incidents);

        $enabledCount = ($settings->usgs_enabled ? 1 : 0) + ($settings->gdacs_enabled ? 1 : 0);
        $status = match (true) {
            count($errors) === 0 => 'ok',
            count($errors) >= max(1, $enabledCount) => 'failed',
            default => 'partial',
        };

        $settings->update([
            'last_run_at' => now(),
            'last_run_status' => $status,
            'last_run_error' => $errors === [] ? null : implode('; ', $errors),
            'last_run_created_count' => $counts['created'],
            'last_run_updated_count' => $counts['updated'],
        ]);
    }
}
