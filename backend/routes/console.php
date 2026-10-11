<?php

use App\Services\Replenishment\HistoryImporter;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Inspiring;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ReplenishmentDemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $ledger->checkpoint();
        $this->info("Records intact: {$r['checked']} entries checked. Latest fingerprint: {$r['latest_hash']}");

        return 0;
    }
    $message = "Check records failed at entry {$r['first_bad_seq']}: {$r['problem']}.";
    $this->error($message);
    // Nobody reads the scheduler's console at 2 a.m.: log it and tell the Head of Office.
    Log::critical($message);
    rescue(fn () => User::where('is_system_admin', true)->pluck('email')->each(
        fn ($email) => Mail::raw($message."\n\nRun \"Check records\" on the Stock page and compare with the fingerprint printed on the last quarterly report.",
            fn ($m) => $m->to($email)->subject('DisasterResource+: stock records check failed'))));

    return 1;
})->purpose('Check records: recompute the stock hash chain and report the first entry that no longer matches');

Artisan::command('data:refresh {--force : Skip the confirmation}', function () {
    if (! $this->option('force') && ! $this->confirm('Delete all stock, requests, history, plans and the catalog (users and settings stay)?')) {
        return 1;
    }

    DB::transaction(function () {
        // The query builder, not Eloquent: StockEntry refuses deletes on purpose.
        // Every table the office's work fills, children before parents (some have foreign keys).
        // Kept: users, roles and permissions, sessions, and the SMTP settings.
        $tables = [
            'app_status_changes', 'app_reservations', 'app_borrows', 'app_stock_conflicts', 'app_stock_counts',
            'app_stock_entries', 'app_chain_checkpoints', 'app_ics_request_lines', 'app_ics_requests', 'app_kit_lines',
            'app_kits', 'app_data_check_flags', 'app_history_lines', 'app_history_imports', 'app_backtests',
            'app_replenishment_runs', 'app_quarter_budgets', 'app_loggers', 'app_resource_types', 'app_depots',
        ];
        foreach ($tables as $table) {
            DB::table($table)->delete();
        }
        // Back to Table 4's proposal, unconfirmed (as the migration created it).
        foreach (['V' => [8, 8, 3], 'E' => [7, 8, 2], 'D' => [6, 8, 1]] as $class => [$k, $m, $w]) {
            DB::table('app_ved_policies')->where('ved_class', $class)->update([
                'coverage_k' => $k, 'coverage_m' => $m, 'weight' => $w,
                'confirmed_by_position' => null, 'confirmed_at' => null, 'updated_at' => now(),
            ]);
        }
    });
    $this->call('db:seed', ['--class' => CatalogSeeder::class, '--force' => true]);
    $this->info('Data cleared; the item catalog, depots and kit are back. Users kept. Load sample history with: php artisan data:mock');

    return 0;
})->purpose('Clear all office data except users and settings, then restore the item catalog');

Artisan::command('data:mock', function () {
    $this->call('db:seed', ['--class' => CatalogSeeder::class, '--force' => true]);
    $this->call('db:seed', ['--class' => ReplenishmentDemoSeeder::class, '--force' => true]);
    $this->info('Mock history loaded: Jul 2023 – Jun 2026, every consumable (items that already had history were skipped).');

    return 0;
})->purpose('Load the mock history and prices (ReplenishmentDemoSeeder) into the current database');

// "Check records" also runs nightly, so a direct database edit is caught even
// if nobody presses the button (system-spec.md §5.4).
Schedule::command('stock:verify')->dailyAt('02:00')->name('stock-verify');
