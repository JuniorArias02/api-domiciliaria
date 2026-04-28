<?php

use Illuminate\Support\Facades\Route;
use Modules\Ingresos\Infrastructure\Http\Controllers\IngresoController;

Route::prefix('v1/ingresos')->middleware('auth:api')->group(function () {
    Route::get('/', [IngresoController::class, 'index']);
    Route::post('/', [IngresoController::class, 'store']);
});
