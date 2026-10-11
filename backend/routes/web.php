<?php

use App\Http\Controllers\Web\DepotController;
use App\Http\Controllers\Web\DivisionController;
use App\Http\Controllers\Web\IcsRequestController;
use App\Http\Controllers\Web\ReplenishmentController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ResourceInventoryController;
use App\Http\Controllers\Web\Settings\UsersController;
use App\Http\Controllers\Web\SmtpSettingsController;
use App\Http\Controllers\Web\StockController;
use Illuminate\Support\Facades\Route;

// The PWA service worker and web manifest, served from the site root so the worker's
// scope covers every page (Vite builds both into public/build/, whose default scope
// would be /build/ only; the worker also precaches the manifest by this root path).
foreach (['sw.js' => 'application/javascript', 'manifest.webmanifest' => 'application/manifest+json'] as $file => $type) {
    Route::get("/{$file}", function () use ($file, $type) {
        $path = public_path("build/{$file}");
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Content-Type' => $type, 'Cache-Control' => 'no-cache']);
    });
}

Route::middleware(['auth', 'active'])->group(function () {
    // The replenishment page is the paper's dashboard (stock, use, recommendation,
    // reasons, budget curve). The Admin passes every role check (EnsureUserHasRole).
    Route::redirect('/', '/replenishment')->name('dashboard');

    Route::middleware(['role:administrative,logistics,operations,research_planning,admin_training'])->group(function () {
        Route::get('/replenishment', [ReplenishmentController::class, 'index'])->name('replenishment.index');
        Route::get('/replenishment/backtest', [ReplenishmentController::class, 'backtest'])->name('replenishment.backtest');
        Route::get('/replenishment/inputs', [ReplenishmentController::class, 'inputs'])->name('replenishment.inputs');
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/ics-requests', [IcsRequestController::class, 'index'])->name('ics-requests.index');
        Route::get('/ics-requests/borrowed', [IcsRequestController::class, 'borrowed'])->name('ics-requests.borrowed');
        Route::get('/resources/types', [ResourceInventoryController::class, 'types'])->name('resources.types');
        Route::get('/depots', [DepotController::class, 'index'])->name('depots.index');
        Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
    });

    Route::middleware(['role:system_admin'])->group(function () {
        Route::get('/settings/users', [UsersController::class, 'index'])->name('settings.users');
        Route::get('/settings/smtp', [SmtpSettingsController::class, 'index'])->name('settings.smtp');
    });
});

require __DIR__.'/auth.php'; // Breeze/Fortify/Jetstream auth scaffolding, if used
