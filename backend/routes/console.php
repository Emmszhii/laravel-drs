<?php

use App\Jobs\AutoResolveIncidentsJob;
use App\Jobs\PollDisasterFeedsJob;
use App\Models\DisasterFeedSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('disaster-feed:poll {--force}', function () {
    $this->comment('Polling disaster feeds...');
    PollDisasterFeedsJob::dispatchSync(manual: (bool) $this->option('force'));
    $this->comment('Done — check app_disasters and Settings → Disaster feed for results.');
})->purpose('Manually poll the configured disaster feeds (USGS/GDACS) synchronously');

// The scheduler only supports fixed frequencies defined in code, not an
// admin-configurable one — so this ticks every 5 minutes (the finest of the
// options in Settings → Disaster feed) and DisasterFeedSetting::dueToPoll()
// decides whether the configured interval has actually elapsed.
Schedule::call(function () {
    if (DisasterFeedSetting::current()->dueToPoll()) {
        PollDisasterFeedsJob::dispatch();
    }
})->everyFiveMinutes()->name('disaster-feed-poll')->withoutOverlapping();

Schedule::job(new AutoResolveIncidentsJob)->hourly()->name('disaster-auto-resolve');
