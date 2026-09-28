<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepotController;
use App\Http\Controllers\Web\DisasterFeedSettingsController;
use App\Http\Controllers\Web\DispatchController;
use App\Http\Controllers\Web\EvacuationCenterController;
use App\Http\Controllers\Web\IcsRequestController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\OptimizationController;
use App\Http\Controllers\Web\PublicDashboardController;
use App\Http\Controllers\Web\ReplenishmentController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ResourceInventoryController;
use App\Http\Controllers\Web\RoutingSettingsController;
use App\Http\Controllers\Web\Settings\UsersController;
use App\Http\Controllers\Web\SmtpSettingsController;
use App\Http\Controllers\Web\StockController;
use Illuminate\Support\Facades\Route;

// Unauthenticated, safe-fields-only view — the "Public users" tier from the
// RBAC feature set. No auth middleware, and Pages/Public/Dashboard.tsx opts
// out of AppLayout since the dashboard chrome doesn't apply here.
Route::get('/public', [PublicDashboardController::class, 'index'])->name('public');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Every CDRRMO division (plus system_admin) gets read access — mirrors
    // the `view-*` permission tier from RoleSeeder.
    Route::middleware(['role:incident_management,ert,logistics,operations,research_planning,admin_training,system_admin'])->group(function () {
        Route::get('/incidents', [IncidentController::class, 'index'])->name('web.incidents.index');
        Route::get('/resources', [ResourceInventoryController::class, 'index'])->name('resources.index');
        Route::get('/resources/types', [ResourceInventoryController::class, 'types'])->name('resources.types');
        Route::get('/resources/loans', [ResourceInventoryController::class, 'loans'])->name('resources.loans');
        Route::get('/requests', [ResourceInventoryController::class, 'requests'])->name('requests.index');
        Route::get('/depots', [DepotController::class, 'index'])->name('depots.index');
        Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch.index');
        Route::get('/evacuation-centers', [EvacuationCenterController::class, 'index'])->name('evacuation-centers.index');
        Route::get('/replenishment', [ReplenishmentController::class, 'index'])->name('replenishment.index');
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/ics-requests', [IcsRequestController::class, 'index'])->name('ics-requests.index');
        Route::get('/ics-requests/borrowed', [IcsRequestController::class, 'borrowed'])->name('ics-requests.borrowed');
    });

    // Matches the `run-optimization` permission holders (RoleSeeder) — was
    // previously only `auth`-gated (no `role:` check at all), letting any
    // authenticated user, including viewer, trigger the solver directly.
    Route::middleware(['role:incident_management,operations,admin_training,system_admin'])->group(function () {
        Route::post('/incidents/{disaster}/optimize', [OptimizationController::class, 'trigger'])
            ->name('incidents.optimize');
    });

    Route::middleware(['role:system_admin'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/settings/users', [UsersController::class, 'index'])->name('settings.users');

        Route::get('/settings/routing', [RoutingSettingsController::class, 'index'])
            ->name('settings.routing');

        Route::get('/settings/disaster-feed', [DisasterFeedSettingsController::class, 'index'])
            ->name('settings.disaster-feed');

        Route::get('/settings/smtp', [SmtpSettingsController::class, 'index'])
            ->name('settings.smtp');
    });
});

require __DIR__.'/auth.php'; // Breeze/Fortify/Jetstream auth scaffolding, if used
