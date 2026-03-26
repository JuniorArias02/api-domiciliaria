<?php

use Illuminate\Support\Facades\Route;
use Modules\Zonas\Infrastructure\Http\Controllers\ZonaController;

Route::prefix('zonas')->middleware('auth:api')->group(function () {
    Route::get('/', [ZonaController::class, 'index']);
    Route::post('/', [ZonaController::class, 'store']);
    Route::put('/{id}', [ZonaController::class, 'update']);
    Route::delete('/{id}', [ZonaController::class, 'destroy']);
});
