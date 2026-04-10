<?php

use Illuminate\Support\Facades\Route;
use Modules\Comunas\Infrastructure\Http\Controllers\ComunaController;

Route::prefix('comunas')->middleware('auth:api')->group(function () {
    Route::get('/', [ComunaController::class, 'index']);
    Route::get('/zona/{id_zona}', [ComunaController::class, 'obtenerPorZona']);
    Route::post('/', [ComunaController::class, 'store']);
    Route::put('/{id}', [ComunaController::class, 'update']);
    Route::delete('/{id}', [ComunaController::class, 'destroy']);
});
