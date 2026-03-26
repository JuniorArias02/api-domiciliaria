<?php

use Illuminate\Support\Facades\Route;
use Modules\Cuidadores\Infrastructure\Http\Controllers\CuidadorController;

Route::prefix('cuidadores')->middleware('auth:api')->group(function () {
    Route::get('/', [CuidadorController::class, 'index']);
    Route::post('/', [CuidadorController::class, 'store']);
    Route::put('/{id}', [CuidadorController::class, 'update']);
    Route::delete('/{id}', [CuidadorController::class, 'destroy']);
});
