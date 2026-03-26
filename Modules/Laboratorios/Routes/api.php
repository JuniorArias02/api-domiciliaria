<?php

use Illuminate\Support\Facades\Route;
use Modules\Laboratorios\Infrastructure\Http\Controllers\LaboratorioController;

Route::prefix('laboratorios')->middleware('auth:api')->group(function () {
    Route::get('/', [LaboratorioController::class, 'index']);
    Route::post('/', [LaboratorioController::class, 'store']);
    Route::put('/{id}', [LaboratorioController::class, 'update']);
    Route::delete('/{id}', [LaboratorioController::class, 'destroy']);
});
