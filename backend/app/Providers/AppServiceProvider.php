<?php

namespace App\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * `laravel/octane` is required only for the deployed container (see
         * backend/Dockerfile's CMD, which calls `octane:start` directly) —
         * but merely having the package installed makes OctaneServiceProvider
         * override `php artisan dev`'s "server" slot to `octane:start --watch`
         * (see Octane::registerDevCommands()). Octane's signal handling
         * there references pcntl's SIGINT/SIGTERM/SIGHUP constants, which
         * don't exist on Windows, crashing `artisan dev` locally. Re-register
         * "server" back to plain `serve` — a userland call always outranks
         * Octane's vendor-priority registration in DevCommands::register(),
         * regardless of provider boot order.
         */
        if (class_exists(\Illuminate\Foundation\DevCommands::class)) {
            \Illuminate\Foundation\DevCommands::artisan('serve', 'server');
        }

        /**
         * REUSABLE API
         */
        Route::macro('module', function (string $prefix, string $controller, string $name) {
            Route::prefix($prefix)->name("{$name}.")->group(function () use ($controller) {
                Route::get('/', [$controller, 'index'])->name('index');
                Route::get('/pagination-search', [$controller, 'paginationSearch'])->name('pagination-search');
                Route::get('/search-active', [$controller, 'searchActive'])->name('search-active');
                Route::get('/lookup', [$controller, 'lookup'])->name('lookup');
                Route::get('/{id}/in-use', [$controller, 'inUse'])->name('in-use');
                Route::post('/', [$controller, 'store'])->name('store');
                Route::post('/{id}', [$controller, 'update'])->name('update'); // post is needed for files uploading
                Route::patch('/{id}/archive', [$controller, 'archive'])->name('archive');
                Route::patch('/{id}/restore', [$controller, 'restore'])->name('restore');
                Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
            });
        });
    }
}
