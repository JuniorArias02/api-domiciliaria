<?php

use Illuminate\Support\Facades\Route;
use Modules\Rutas\Infrastructure\Http\Controllers\RutaController;

Route::prefix('rutas')->middleware('auth:api')->group(function () {
    Route::get('/', [RutaController::class, 'index']);
    Route::post('/', [RutaController::class, 'store']);
    Route::get('/exportar/excel', [RutaController::class, 'exportExcel']);
    Route::get('/{id}', [RutaController::class, 'show']);
    Route::put('/{id}', [RutaController::class, 'update']);
    Route::delete('/{id}', [RutaController::class, 'destroy']);
    Route::patch('/{id}/asignar', [RutaController::class, 'asignar']);
});
