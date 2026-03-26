<?php

use Illuminate\Support\Facades\Route;
use Modules\Pacientes\Infrastructure\Http\Controllers\PacienteController;

Route::prefix('pacientes')->middleware('auth:api')->group(function () {
    Route::post('/', [PacienteController::class, 'store']);
    Route::put('/{id}', [PacienteController::class, 'update']);
    Route::delete('/{id}', [PacienteController::class, 'destroy']);
    Route::patch('/{id}/ubicacion', [PacienteController::class, 'updateUbicacion']);
});
