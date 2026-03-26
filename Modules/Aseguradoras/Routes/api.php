<?php

use Illuminate\Support\Facades\Route;
use Modules\Aseguradoras\Infrastructure\Http\Controllers\AseguradoraController;

Route::prefix('aseguradoras')->middleware('auth:api')->group(function () {
    Route::get('/', [AseguradoraController::class, 'index']);
    Route::post('/', [AseguradoraController::class, 'store']);
    Route::put('/{id}', [AseguradoraController::class, 'update']);
    Route::delete('/{id}', [AseguradoraController::class, 'destroy']);
});
