<?php

use Illuminate\Support\Facades\Route;
use Modules\SolicitudesEquipos\Infrastructure\Http\Controllers\SolicitudEquipoController;

Route::prefix('solicitudes-equipos')->middleware('auth:api')->group(function () {
    Route::get('/', [SolicitudEquipoController::class, 'index']);
    Route::post('/', [SolicitudEquipoController::class, 'store']);
    Route::put('/{id}', [SolicitudEquipoController::class, 'update']);
    Route::delete('/{id}', [SolicitudEquipoController::class, 'destroy']);
});
