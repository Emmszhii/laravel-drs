<?php

use App\Http\Controllers\Api\IncidentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('incidents', IncidentController::class)->only(['index', 'show', 'store']);

    // Route::apiResource('depots', \App\Http\Controllers\Api\DepotController::class);
    // Route::apiResource('resource-types', \App\Http\Controllers\Api\ResourceTypeController::class);
    // Route::post('/requests/{request}/approve', [\App\Http\Controllers\Api\RequestController::class, 'approve']);
});
