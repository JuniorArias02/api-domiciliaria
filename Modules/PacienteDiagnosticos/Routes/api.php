<?php

use Illuminate\Support\Facades\Route;
use Modules\PacienteDiagnosticos\Infrastructure\Http\Controllers\PacienteDiagnosticoController;

Route::prefix('paciente-diagnosticos')->middleware('auth:api')->group(function () {
    Route::get('/', [PacienteDiagnosticoController::class, 'index']);
    Route::post('/', [PacienteDiagnosticoController::class, 'store']);
    Route::put('/{id_paciente}/{codigo_cie10}/{tipo_diagnostico}/{id_visita}', [PacienteDiagnosticoController::class, 'update']);
    Route::delete('/{id_paciente}/{codigo_cie10}/{tipo_diagnostico}/{id_visita}', [PacienteDiagnosticoController::class, 'destroy']);
});
