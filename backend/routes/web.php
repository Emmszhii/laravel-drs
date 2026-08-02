<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepotController;
use App\Http\Controllers\Web\DisasterFeedSettingsController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\OptimizationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ResourceInventoryController;
use App\Http\Controllers\Web\RoutingSettingsController;
use App\Http\Controllers\Web\Settings\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/incidents/{disaster}/optimize', [OptimizationController::class, 'trigger'])
        ->name('incidents.optimize');

    Route::middleware(['role:admin,developer'])->group(function () {
        Route::get('/incidents', [IncidentController::class, 'index'])->name('web.incidents.index');
        Route::get('/resources', [ResourceInventoryController::class, 'index'])->name('resources.index');
        Route::get('/resources/types', [ResourceInventoryController::class, 'types'])->name('resources.types');
        Route::get('/depots', [DepotController::class, 'index'])->name('depots.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/settings/users', [UsersController::class, 'index'])->name('settings.users');

        Route::get('/settings/routing', [RoutingSettingsController::class, 'index'])
            ->name('settings.routing');

        Route::get('/settings/disaster-feed', [DisasterFeedSettingsController::class, 'index'])
            ->name('settings.disaster-feed');
    });
});

require __DIR__.'/auth.php'; // Breeze/Fortify/Jetstream auth scaffolding, if used
