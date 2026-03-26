<?php

use Illuminate\Support\Facades\Route;
use Modules\Especialidades\Infrastructure\Http\Controllers\EspecialidadController;

Route::prefix('especialidades')->middleware('auth:api')->group(function () {
    Route::get('/', [EspecialidadController::class, 'index']);
    Route::post('/', [EspecialidadController::class, 'store']);
    Route::put('/{id}', [EspecialidadController::class, 'update']);
    Route::delete('/{id}', [EspecialidadController::class, 'destroy']);
});
