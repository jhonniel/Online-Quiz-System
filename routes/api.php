<?php

use App\Http\Controllers\Api\ExternalApiMonitoringController;
use Illuminate\Support\Facades\Route;

Route::middleware(['external.api.key'])->group(function () {
    Route::get('/monitoring/endpoints', [ExternalApiMonitoringController::class, 'endpoints'])
        ->name('api.external.monitoring.endpoints');
});
