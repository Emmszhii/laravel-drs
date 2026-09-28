<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\DirectionsController;
use App\Http\Controllers\Api\DisasterFeedSettingController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\EvacuationCenterController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReplenishmentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\ResourceLoanController;
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
    // tier (view-incidents permission / the read-only role group on the web
    // /incidents route).
    Route::middleware(['role:incident_management,ert,logistics,operations,research_planning,admin_training,system_admin'])->group(function () {
        Route::get('/incidents/pagination-search', [IncidentController::class, 'paginationSearch']);
    });

    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);

    Route::middleware(['role:incident_management,operations,admin_training,system_admin'])->group(function () {
        Route::post('/incidents', [IncidentController::class, 'store']);
        Route::post('/incidents/{incident}/areas', [IncidentController::class, 'storeArea']);
        Route::post('/incidents/{incident}/areas/{area}', [IncidentController::class, 'updateArea']);
    });

    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('/activity-log', [ActivityLogController::class, 'index']);

    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/requests/pagination-search', [RequestController::class, 'paginationSearch']);

    Route::middleware(['role:incident_management,operations,admin_training,system_admin'])->group(function () {
        Route::post('/requests', [RequestController::class, 'store']);
    });

    // Department-head sign-off (ICS 213-RR) — Logistics owns approval, plus
    // Admin & Training / system_admin.
    Route::middleware(['role:logistics,admin_training,system_admin'])->group(function () {
        Route::patch('/requests/{request}/approve', [RequestController::class, 'approve']);
        Route::patch('/requests/{request}/reject', [RequestController::class, 'reject']);
    });

    Route::get('/directions', [DirectionsController::class, 'index']);

    // Read-only access for every division.
    Route::middleware(['role:incident_management,ert,logistics,operations,research_planning,admin_training,system_admin'])->group(function () {
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
        // coordinator+ tier, may report a position/status update.
        Route::post('/dispatches/{dispatch}/position', [DispatchController::class, 'updatePosition']);
        Route::post('/dispatches/{dispatch}/status', [DispatchController::class, 'updateStatus']);

        Route::get('/evacuation-centers', [EvacuationCenterController::class, 'index']);
        Route::get('/evacuation-centers/pagination-search', [EvacuationCenterController::class, 'paginationSearch']);
    });

    Route::middleware(['role:incident_management,operations,admin_training,system_admin'])->group(function () {
        Route::post('/dispatches', [DispatchController::class, 'store']);

        Route::post('/evacuation-centers', [EvacuationCenterController::class, 'store']);
        Route::post('/evacuation-centers/{id}', [EvacuationCenterController::class, 'update']);
        Route::delete('/evacuation-centers/{id}', [EvacuationCenterController::class, 'destroy']);
    });

    // Logistics owns depot/resource stock management, plus system_admin.
    Route::middleware(['role:logistics,admin_training,system_admin'])->group(function () {
        Route::get('/resource-loans', [ResourceLoanController::class, 'index']);
        Route::post('/resource-loans', [ResourceLoanController::class, 'store']);
        Route::post('/resource-loans/{id}', [ResourceLoanController::class, 'update']);
    });

    // Quarterly replenishment (backend/docs/system-spec.md): every division
    // can read runs and flags; Logistics only encodes (history import, flag
    // resolution); the Admin runs the solver and approves or rejects.
    Route::middleware(['role:incident_management,ert,logistics,operations,research_planning,admin_training,system_admin'])->group(function () {
        Route::get('/replenishment/runs', [ReplenishmentController::class, 'runs']);
        Route::get('/replenishment/runs/{run}', [ReplenishmentController::class, 'show']);
        Route::get('/replenishment/flags', [ReplenishmentController::class, 'flags']);
    });
    Route::middleware(['role:logistics'])->group(function () {
        Route::post('/replenishment/history/import', [ReplenishmentController::class, 'importHistory']);
        Route::post('/replenishment/flags/{flag}/resolve', [ReplenishmentController::class, 'resolveFlag']);
    });
    Route::middleware(['role:system_admin'])->group(function () {
        Route::post('/replenishment/runs', [ReplenishmentController::class, 'run']);
        Route::post('/replenishment/runs/{run}/approve', [ReplenishmentController::class, 'approve']);
        Route::post('/replenishment/runs/{run}/reject', [ReplenishmentController::class, 'reject']);
    });

    Route::middleware(['role:system_admin'])->group(function () {
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
