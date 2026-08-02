<?php

use App\Http\Controllers\Api\DirectionsController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\RoutingSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('incidents', IncidentController::class)->only(['index', 'show', 'store']);

    Route::get('/directions', [DirectionsController::class, 'index']);

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/routing-settings', [RoutingSettingController::class, 'show']);
        Route::patch('/routing-settings', [RoutingSettingController::class, 'update']);
        Route::post('/routing-settings/test', [RoutingSettingController::class, 'test']);
    });

    // Route::apiResource('depots', \App\Http\Controllers\Api\DepotController::class);
    // Route::apiResource('resource-types', \App\Http\Controllers\Api\ResourceTypeController::class);
    // Route::post('/requests/{request}/approve', [\App\Http\Controllers\Api\RequestController::class, 'approve']);
});
