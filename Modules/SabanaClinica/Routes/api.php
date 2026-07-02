<?php

use Illuminate\Support\Facades\Route;
use Modules\SabanaClinica\Infrastructure\Http\Controllers\SabanaClinicaController;

Route::prefix('sabana-clinica')->middleware('auth:api')->group(function () {
    Route::get('/', [SabanaClinicaController::class, 'index']);
    Route::post('/', [SabanaClinicaController::class, 'store']);
    Route::get('/{id}', [SabanaClinicaController::class, 'show']);
    Route::patch('/{id}', [SabanaClinicaController::class, 'update']);
    Route::delete('/{id}', [SabanaClinicaController::class, 'destroy']);
});
