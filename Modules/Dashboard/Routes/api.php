<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Infrastructure\Http\Controllers\DashboardController;

Route::prefix('dashboard')->middleware('auth:api')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/indicadores', [DashboardController::class, 'indicadores']);
});
