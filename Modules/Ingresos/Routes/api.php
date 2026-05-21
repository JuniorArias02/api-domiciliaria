<?php

use Illuminate\Support\Facades\Route;
use Modules\Ingresos\Infrastructure\Http\Controllers\IngresoController;

Route::prefix('ingresos')->middleware('auth:api')->group(function () {
    Route::get('/', [IngresoController::class, 'index']);
    Route::get('/buscar', [IngresoController::class, 'buscar']);
    Route::get('/verificar-autorizacion', [IngresoController::class, 'verificarAutorizacion']);
    Route::post('/', [IngresoController::class, 'store']);
    Route::post('/con-sesiones', [IngresoController::class, 'crearConSesiones']);
    Route::get('/paciente/{idPaciente}/autorizaciones', [IngresoController::class, 'autorizacionesPorPaciente']);
});
