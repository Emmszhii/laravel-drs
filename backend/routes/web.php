<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RoutingSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings/routing', [RoutingSettingsController::class, 'index'])
        ->middleware('role:admin')
        ->name('settings.routing');

    // Route::get('/incidents', [\App\Http\Controllers\Web\IncidentController::class, 'index'])->name('incidents.index');
    // Route::get('/resources', [\App\Http\Controllers\Web\ResourceController::class, 'index'])->name('resources.index');
    // Route::get('/depots', [\App\Http\Controllers\Web\DepotController::class, 'index'])->name('depots.index');
    // Route::get('/settings/users', [\App\Http\Controllers\Web\UserController::class, 'index'])->name('settings.users');
});

require __DIR__.'/auth.php'; // Breeze/Fortify/Jetstream auth scaffolding, if used
