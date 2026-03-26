<?php

use Illuminate\Support\Facades\Route;
use Modules\Cargos\Infrastructure\Http\Controllers\CargoController;

Route::prefix('cargos')->middleware('auth:api')->group(function () {
    Route::get('/', [CargoController::class, 'index']);
    Route::post('/', [CargoController::class, 'store']);
    Route::put('/{id}', [CargoController::class, 'update']);
    Route::delete('/{id}', [CargoController::class, 'destroy']);
});
