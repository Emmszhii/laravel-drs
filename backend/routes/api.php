<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\DirectionsController;
use App\Http\Controllers\Api\DisasterFeedSettingController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\EvacuationCenterController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\ResourceTypeController;
use App\Http\Controllers\Api\RoutingSettingController;
use App\Http\Controllers\Api\Settings\UserController;
use App\Http\Controllers\Api\SmtpSettingController;
use Illuminate\Support\Facades\Route;

/**
 * CRUD modules below all follow a fixed convention (matches
 * resources/js/api-service/http.ts's createModuleResource):
 *   GET    /{resource}                    index
 *   GET    /{resource}/pagination-search  paginationSearch
 *   POST   /{resource}                    store
 *   POST   /{resource}/{id}               update (not PUT/PATCH — see http.ts)
 *   DELETE /{resource}/{id}                destroy
 */
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    // Static-segment routes (pagination-search) MUST be registered before the
    // {incident} wildcard route below, or Laravel matches the wildcard first
    // and treats "pagination-search" as an incident ID. Matches the read-only
    // tier (view-incidents permission / role:...,coordinator,respondent group
    // on the web /incidents route) — this used to be admin,developer-only,
    // leaving coordinator/respondent with a permanently empty incidents list
    // despite having page access.
    Route::middleware(['role:admin,developer,coordinator,respondent'])->group(function () {
        Route::get('/incidents/pagination-search', [IncidentController::class, 'paginationSearch']);
    });

    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);

    Route::middleware(['role:admin,developer,coordinator'])->group(function () {
        Route::post('/incidents', [IncidentController::class, 'store']);
        Route::post('/incidents/{incident}/areas', [IncidentController::class, 'storeArea']);
        Route::post('/incidents/{incident}/areas/{area}', [IncidentController::class, 'updateArea']);
    });

    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('/activity-log', [ActivityLogController::class, 'index']);

    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/requests/pagination-search', [RequestController::class, 'paginationSearch']);

    Route::middleware(['role:admin,developer,coordinator'])->group(function () {
        Route::post('/requests', [RequestController::class, 'store']);
    });

    Route::get('/directions', [DirectionsController::class, 'index']);

    // Read-only access for coordinator/respondent, in addition to admin/developer.
    Route::middleware(['role:admin,developer,coordinator,respondent'])->group(function () {
        Route::get('/depots', [DepotController::class, 'index']);
        Route::get('/depots/pagination-search', [DepotController::class, 'paginationSearch']);

        Route::get('/resource-types', [ResourceTypeController::class, 'index']);
        Route::get('/resource-types/pagination-search', [ResourceTypeController::class, 'paginationSearch']);

        Route::get('/resources', [ResourceController::class, 'index']);
        Route::get('/resources/pagination-search', [ResourceController::class, 'paginationSearch']);

        Route::get('/dispatches', [DispatchController::class, 'index']);
        Route::get('/dispatches/pagination-search', [DispatchController::class, 'paginationSearch']);
        Route::get('/dispatches/drivers', [DispatchController::class, 'drivers']);
        // Self-authorized inside the controller: the assigned driver, or a
        // coordinator+, may report a position/status update.
        Route::post('/dispatches/{dispatch}/position', [DispatchController::class, 'updatePosition']);
        Route::post('/dispatches/{dispatch}/status', [DispatchController::class, 'updateStatus']);

        Route::get('/evacuation-centers', [EvacuationCenterController::class, 'index']);
        Route::get('/evacuation-centers/pagination-search', [EvacuationCenterController::class, 'paginationSearch']);
    });

    Route::middleware(['role:admin,developer,coordinator'])->group(function () {
        Route::post('/dispatches', [DispatchController::class, 'store']);

        Route::post('/evacuation-centers', [EvacuationCenterController::class, 'store']);
        Route::post('/evacuation-centers/{id}', [EvacuationCenterController::class, 'update']);
        Route::delete('/evacuation-centers/{id}', [EvacuationCenterController::class, 'destroy']);
    });

    Route::middleware(['role:admin,developer'])->group(function () {
        Route::get('/routing-settings', [RoutingSettingController::class, 'show']);
        Route::patch('/routing-settings', [RoutingSettingController::class, 'update']);
        Route::post('/routing-settings/test', [RoutingSettingController::class, 'test']);

        Route::get('/disaster-feed-settings', [DisasterFeedSettingController::class, 'show']);
        Route::patch('/disaster-feed-settings', [DisasterFeedSettingController::class, 'update']);
        Route::post('/disaster-feed-settings/run', [DisasterFeedSettingController::class, 'run']);

        Route::get('/smtp-settings', [SmtpSettingController::class, 'show']);
        Route::patch('/smtp-settings', [SmtpSettingController::class, 'update']);
        Route::post('/smtp-settings/test', [SmtpSettingController::class, 'test']);

        Route::post('/incidents/{incident}', [IncidentController::class, 'update']);
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);
        Route::delete('/incidents/{incident}/areas/{area}', [IncidentController::class, 'destroyArea']);

        Route::post('/depots', [DepotController::class, 'store']);
        Route::post('/depots/{id}', [DepotController::class, 'update']);
        Route::delete('/depots/{id}', [DepotController::class, 'destroy']);

        Route::post('/resource-types', [ResourceTypeController::class, 'store']);
        Route::post('/resource-types/{id}', [ResourceTypeController::class, 'update']);
        Route::delete('/resource-types/{id}', [ResourceTypeController::class, 'destroy']);

        Route::post('/resources', [ResourceController::class, 'store']);
        Route::post('/resources/{id}', [ResourceController::class, 'update']);
        Route::delete('/resources/{id}', [ResourceController::class, 'destroy']);

        Route::get('/settings/users', [UserController::class, 'index']);
        Route::get('/settings/users/pagination-search', [UserController::class, 'paginationSearch']);
        Route::post('/settings/users', [UserController::class, 'store']);
        Route::post('/settings/users/{id}', [UserController::class, 'update']);
        Route::post('/settings/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::post('/settings/users/{id}/activate', [UserController::class, 'activate']);
        Route::post('/settings/users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::delete('/settings/users/{id}', [UserController::class, 'destroy']);

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/pagination-search', [ReportController::class, 'paginationSearch']);
        Route::get('/reports/{report}/download', [ReportController::class, 'download']);
        Route::post('/reports', [ReportController::class, 'store']);
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
    });
});
