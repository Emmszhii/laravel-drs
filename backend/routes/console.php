<?php

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

// "Check records" also runs nightly, so a direct database edit is caught even
// if nobody presses the button (system-spec.md §5.4).
Schedule::command('stock:verify')->dailyAt('02:00')->name('stock-verify');
