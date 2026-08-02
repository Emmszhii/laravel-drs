<?php

use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\DirectionsController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\ResourceTypeController;
use App\Http\Controllers\Api\RoutingSettingController;
use App\Http\Controllers\Api\Settings\UserController;
use Illuminate\Support\Facades\Route;

/**
 * CRUD modules below all follow a fixed convention (matches
 * resources/js/api-service/http.ts's createCrudResource):
 *   GET    /{resource}                    index
 *   GET    /{resource}/pagination-search  paginationSearch
 *   POST   /{resource}                    store
 *   POST   /{resource}/{id}               update (not PUT/PATCH — see http.ts)
 *   DELETE /{resource}/{id}                destroy
 */
Route::middleware(['auth:sanctum'])->group(function () {
    // Static-segment routes (pagination-search) MUST be registered before the
    // {incident} wildcard route below, or Laravel matches the wildcard first
    // and treats "pagination-search" as an incident ID.
    Route::middleware(['role:admin,developer'])->group(function () {
        Route::get('/incidents/pagination-search', [IncidentController::class, 'paginationSearch']);
    });

    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::post('/incidents', [IncidentController::class, 'store']);

    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('/directions', [DirectionsController::class, 'index']);

    Route::middleware(['role:admin,developer'])->group(function () {
        Route::get('/routing-settings', [RoutingSettingController::class, 'show']);
        Route::patch('/routing-settings', [RoutingSettingController::class, 'update']);
        Route::post('/routing-settings/test', [RoutingSettingController::class, 'test']);

        Route::post('/incidents/{incident}', [IncidentController::class, 'update']);
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);
        Route::post('/incidents/{incident}/areas', [IncidentController::class, 'storeArea']);
        Route::delete('/incidents/{incident}/areas/{area}', [IncidentController::class, 'destroyArea']);

        Route::get('/depots', [DepotController::class, 'index']);
        Route::get('/depots/pagination-search', [DepotController::class, 'paginationSearch']);
        Route::post('/depots', [DepotController::class, 'store']);
        Route::post('/depots/{id}', [DepotController::class, 'update']);
        Route::delete('/depots/{id}', [DepotController::class, 'destroy']);

        Route::get('/resource-types', [ResourceTypeController::class, 'index']);
        Route::get('/resource-types/pagination-search', [ResourceTypeController::class, 'paginationSearch']);
        Route::post('/resource-types', [ResourceTypeController::class, 'store']);
        Route::post('/resource-types/{id}', [ResourceTypeController::class, 'update']);
        Route::delete('/resource-types/{id}', [ResourceTypeController::class, 'destroy']);

        Route::get('/resources', [ResourceController::class, 'index']);
        Route::get('/resources/pagination-search', [ResourceController::class, 'paginationSearch']);
        Route::post('/resources', [ResourceController::class, 'store']);
        Route::post('/resources/{id}', [ResourceController::class, 'update']);
        Route::delete('/resources/{id}', [ResourceController::class, 'destroy']);

        Route::get('/settings/users', [UserController::class, 'index']);
        Route::get('/settings/users/pagination-search', [UserController::class, 'paginationSearch']);
        Route::post('/settings/users', [UserController::class, 'store']);
        Route::post('/settings/users/{id}', [UserController::class, 'update']);
        Route::delete('/settings/users/{id}', [UserController::class, 'destroy']);

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/pagination-search', [ReportController::class, 'paginationSearch']);
        Route::get('/reports/{report}/download', [ReportController::class, 'download']);
        Route::post('/reports', [ReportController::class, 'store']);
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
    });
});
