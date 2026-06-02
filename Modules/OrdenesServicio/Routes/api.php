<?php

use Illuminate\Support\Facades\Route;
use Modules\OrdenesServicio\Infrastructure\Http\Controllers\OrdenServicioController;

Route::prefix('ordenes-servicio')->middleware('auth:api')->group(function () {
    Route::get('/pendientes-por-autorizacion', [OrdenServicioController::class, 'serviciosPendientesPorAutorizacion']);
    Route::get('/', [OrdenServicioController::class, 'index']);
    Route::post('/', [OrdenServicioController::class, 'store']);
});
