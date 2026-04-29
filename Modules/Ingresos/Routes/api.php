<?php

use Illuminate\Support\Facades\Route;
use Modules\Ingresos\Infrastructure\Http\Controllers\IngresoController;

Route::prefix('ingresos')->middleware('auth:api')->group(function () {
    Route::get('/', [IngresoController::class, 'index']);
    Route::post('/', [IngresoController::class, 'store']);
    Route::get('/paciente/{idPaciente}/autorizaciones', [IngresoController::class, 'autorizacionesPorPaciente']);
});
