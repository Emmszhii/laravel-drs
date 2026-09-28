<?php

use App\Jobs\AutoResolveIncidentsJob;
use App\Jobs\PollDisasterFeedsJob;
use App\Jobs\SnapshotQuarterlyStockJob;
use App\Models\DisasterFeedSetting;
use App\Models\Resource;
use App\Models\StockEntry;
use App\Services\Replenishment\HistoryImporter;
use App\Services\Stock\StockLedger;
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

Artisan::command('stock:verify', function (StockLedger $ledger) {
    $r = $ledger->verify();
    if ($r['ok']) {
        $this->info("Records intact: {$r['checked']} entries checked. Latest fingerprint: {$r['latest_hash']}");

        return 0;
    }
    $this->error("Check failed at entry {$r['first_bad_seq']}: {$r['problem']}.");

    return 1;
})->purpose('Check records: recompute the stock hash chain and report the first entry that no longer matches');

// One-time move from the old aggregate stock rows to the append-only ledger.
Artisan::command('stock:opening-balance', function (StockLedger $ledger) {
    if (StockEntry::exists()) {
        $this->warn('The ledger already has entries; opening balances were not written.');

        return 1;
    }
    $n = 0;
    foreach (Resource::where('quantity_available', '>', 0)->get() as $r) {
        $ledger->record(['resource_type_id' => $r->resource_type_id, 'depot_id' => $r->depot_id, 'quantity' => $r->quantity_available, 'reason' => 'opening_balance'], null);
        $n++;
    }
    $this->info("Wrote {$n} opening-balance entr" . ($n === 1 ? 'y' : 'ies') . '.');

    return 0;
})->purpose('Write one opening-balance stock entry per existing depot stock row (ledger must be empty)');

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

// "Check records" also runs nightly, so a direct database edit is caught even
// if nobody presses the button (system-spec.md §5.4).
Schedule::command('stock:verify')->dailyAt('02:00')->name('stock-verify');
