<?php

use Illuminate\Support\Facades\Route;
use Modules\Servicios\Infrastructure\Http\Controllers\ServicioController;

Route::prefix('servicios')->middleware('auth:api')->group(function () {
    Route::get('/', [ServicioController::class, 'index']);
    Route::get('/{id}', [ServicioController::class, 'show']);
    Route::post('/', [ServicioController::class, 'store']);
    Route::put('/{id}', [ServicioController::class, 'update']);
    Route::delete('/{id}', [ServicioController::class, 'destroy']);
});
