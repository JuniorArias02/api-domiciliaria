<?php

use Illuminate\Support\Facades\Route;
use Modules\RegistroPrograma\Infrastructure\Http\Controllers\RegistroProgramaController;

Route::prefix('registro-programa')->middleware('auth:api')->group(function () {
    Route::get('/pacientes', [RegistroProgramaController::class, 'getPacientes']);
    Route::get('/pacientes/{id}/autorizaciones', [RegistroProgramaController::class, 'getAutorizaciones']);
    Route::get('/ingreso/{ingreso}/orden-medica', [RegistroProgramaController::class, 'getOrdenMedicaPorIngreso']);
});
