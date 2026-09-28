<?php

use App\Jobs\AutoResolveIncidentsJob;
use App\Jobs\PollDisasterFeedsJob;
use App\Jobs\SnapshotQuarterlyStockJob;
use App\Models\DisasterFeedSetting;
use App\Services\Replenishment\HistoryImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('history:import {file}', function (HistoryImporter $importer) {
    $result = $importer->import($this->argument('file'));
    foreach ($result['errors'] as $error) {
        $this->error($error);
    }
    $this->info("Imported {$result['imported']} history line(s).");

    return $result['errors'] ? 1 : 0;
})->purpose('Import encoded ICS 213-RR history (docs/templates/history-encoding-template.csv)');

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

// Formalizes CDRRMO's manual Q1-Q4 stock-review practice (see 2026-09-03
// interview) — runs the day a new quarter begins, snapshotting the quarter
// that just closed. Feeds ReorderSuggestionCalculator; advisory only.
Schedule::job(new SnapshotQuarterlyStockJob)->quarterly()->name('quarterly-stock-snapshot');
