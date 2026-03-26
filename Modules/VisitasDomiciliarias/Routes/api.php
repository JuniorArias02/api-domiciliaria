<?php

use Illuminate\Support\Facades\Route;
use Modules\VisitasDomiciliarias\Infrastructure\Http\Controllers\VisitaDomiciliariaController;

Route::prefix('visitas-domiciliarias')->middleware('auth:api')->group(function () {
    Route::get('/', [VisitaDomiciliariaController::class, 'index']);
    Route::post('/', [VisitaDomiciliariaController::class, 'store']);
    Route::put('/{id}', [VisitaDomiciliariaController::class, 'update']);
    Route::delete('/{id}', [VisitaDomiciliariaController::class, 'destroy']);
});
