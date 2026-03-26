<?php

use Illuminate\Support\Facades\Route;
use Modules\Barrios\Infrastructure\Http\Controllers\BarrioController;

Route::prefix('barrios')->middleware('auth:api')->group(function () {
    Route::get('/', [BarrioController::class, 'index']);
    Route::post('/', [BarrioController::class, 'store']);
    Route::put('/{id}', [BarrioController::class, 'update']);
    Route::delete('/{id}', [BarrioController::class, 'destroy']);
});
