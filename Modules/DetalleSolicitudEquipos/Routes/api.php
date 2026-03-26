<?php

use Illuminate\Support\Facades\Route;
use Modules\DetalleSolicitudEquipos\Infrastructure\Http\Controllers\DetalleSolicitudEquipoController;

Route::prefix('detalle-solicitudes-equipos')->middleware('auth:api')->group(function () {
    Route::get('/', [DetalleSolicitudEquipoController::class, 'index']);
    Route::post('/', [DetalleSolicitudEquipoController::class, 'store']);
    Route::put('/{id}', [DetalleSolicitudEquipoController::class, 'update']);
    Route::delete('/{id}', [DetalleSolicitudEquipoController::class, 'destroy']);
});
