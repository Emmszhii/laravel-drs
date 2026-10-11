<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\IcsRequestController;
use App\Http\Controllers\Api\KitController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReplenishmentController;
use App\Http\Controllers\Api\ResourceTypeController;
use App\Http\Controllers\Api\Settings\UserController;
use App\Http\Controllers\Api\SmtpSettingController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

/**
 * CRUD modules below all follow a fixed convention (matches
 * resources/js/api-service/http.ts's createModuleResource):
 *   GET    /{resource}                    index
 *   GET    /{resource}/pagination-search  paginationSearch
 *   POST   /{resource}                    store
 *   POST   /{resource}/{id}               update (not PUT/PATCH — see http.ts)
 *   DELETE /{resource}/{id}                destroy
 *
 * Roles (backend/docs/system-spec.md §2): the Admin (is_system_admin, the
 * Head of Office) passes every `role:` check (EnsureUserHasRole), so
 * `role:system_admin` means Admin only. Logistics encodes; requesters file;
 * the Admin enters the budget, runs the solver and approves.
 */
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('/activity-log', [ActivityLogController::class, 'index']);

    // Read-only access for every role.
    Route::middleware(['role:administrative,logistics,operations,research_planning,admin_training'])->group(function () {
        Route::get('/depots', [DepotController::class, 'index']);
        Route::get('/depots/pagination-search', [DepotController::class, 'paginationSearch']);

        Route::get('/divisions', [DivisionController::class, 'index']);
        Route::get('/divisions/pagination-search', [DivisionController::class, 'paginationSearch']);

        Route::get('/resource-types', [ResourceTypeController::class, 'index']);
        Route::get('/resource-types/pagination-search', [ResourceTypeController::class, 'paginationSearch']);

        Route::get('/replenishment/runs', [ReplenishmentController::class, 'runs']);
        Route::get('/replenishment/runs/{run}', [ReplenishmentController::class, 'show']);
        Route::get('/replenishment/flags', [ReplenishmentController::class, 'flags']);
        Route::get('/replenishment/backtest', [ReplenishmentController::class, 'latestBacktest']);

        Route::get('/ics-requests', [IcsRequestController::class, 'index']);
        Route::get('/borrows', [IcsRequestController::class, 'borrows']);

        Route::get('/stock/levels', [StockController::class, 'levels']);
    });

    // Admin and Training keeps the item catalog, the depot list and the divisions.
    Route::middleware(['role:admin_training'])->group(function () {
        Route::post('/depots', [DepotController::class, 'store']);
        Route::post('/depots/{id}', [DepotController::class, 'update']);
        Route::delete('/depots/{id}', [DepotController::class, 'destroy']);

        Route::post('/divisions', [DivisionController::class, 'store']);
        Route::post('/divisions/{id}', [DivisionController::class, 'update']);
        Route::delete('/divisions/{id}', [DivisionController::class, 'destroy']);

        Route::post('/resource-types', [ResourceTypeController::class, 'store']);
        Route::post('/resource-types/{id}', [ResourceTypeController::class, 'update']);
        Route::delete('/resource-types/{id}', [ResourceTypeController::class, 'destroy']);
    });

    // Requesters file ICS 213-RR requests.
    Route::middleware(['role:logistics,operations'])->group(function () {
        Route::post('/ics-requests', [IcsRequestController::class, 'store']);
        Route::post('/ics-requests/{ics}/submit', [IcsRequestController::class, 'submit']);
        Route::post('/ics-requests/{ics}/cancel', [IcsRequestController::class, 'cancel']);
    });

    Route::middleware(['role:logistics,research_planning'])->group(function () {
        Route::get('/replenishment/report', [ReplenishmentController::class, 'report']);
    });

    // Logistics encodes: history, data-check flags, stock entries, releases, the borrowed-item logbook.
    Route::middleware(['role:logistics'])->group(function () {
        Route::post('/replenishment/history/import', [ReplenishmentController::class, 'importHistory']);
        Route::post('/replenishment/flags/{flag}/resolve', [ReplenishmentController::class, 'resolveFlag']);

        Route::post('/ics-requests/{ics}/release', [IcsRequestController::class, 'release']);
        Route::post('/ics-requests/{ics}/close', [IcsRequestController::class, 'close']);
        Route::post('/ics-requests/{ics}/remarks', [IcsRequestController::class, 'remarks']);
        Route::post('/borrows', [IcsRequestController::class, 'lend']);
        Route::post('/borrows/{borrow}/status', [IcsRequestController::class, 'borrowStatus']);
        Route::post('/kits', [KitController::class, 'store']);
        Route::delete('/kits/{kit}', [KitController::class, 'destroy']);

        Route::post('/stock/sync', [StockController::class, 'sync']);
        Route::get('/stock/conflicts', [StockController::class, 'conflicts']);
        Route::get('/stock/verify', [StockController::class, 'verify']);
        Route::post('/stock/conflicts/{conflict}/resolve', [StockController::class, 'resolveConflict']);
        Route::post('/stock/counts', [StockController::class, 'storeCount']);
    });

    // Admin only.
    Route::middleware(['role:system_admin'])->group(function () {
        Route::post('/replenishment/runs', [ReplenishmentController::class, 'run']);
        Route::post('/replenishment/runs/{run}/approve', [ReplenishmentController::class, 'approve']);
        Route::post('/replenishment/runs/{run}/reject', [ReplenishmentController::class, 'reject']);
        Route::post('/replenishment/backtest', [ReplenishmentController::class, 'backtest']);
        Route::patch('/replenishment/policies/{policy}', [ReplenishmentController::class, 'updatePolicy']);

        Route::post('/ics-requests/{ics}/approve', [IcsRequestController::class, 'approve']);
        Route::post('/ics-requests/{ics}/reject', [IcsRequestController::class, 'reject']);

        Route::get('/smtp-settings', [SmtpSettingController::class, 'show']);
        Route::patch('/smtp-settings', [SmtpSettingController::class, 'update']);
        Route::post('/smtp-settings/test', [SmtpSettingController::class, 'test']);

        Route::get('/settings/users', [UserController::class, 'index']);
        Route::get('/settings/users/pagination-search', [UserController::class, 'paginationSearch']);
        Route::post('/settings/users', [UserController::class, 'store']);
        Route::post('/settings/users/{id}', [UserController::class, 'update']);
        Route::post('/settings/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::post('/settings/users/{id}/activate', [UserController::class, 'activate']);
        Route::post('/settings/users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::delete('/settings/users/{id}', [UserController::class, 'destroy']);
    });
});
