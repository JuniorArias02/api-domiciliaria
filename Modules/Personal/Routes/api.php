<?php

use Illuminate\Support\Facades\Route;
use Modules\Personal\Infrastructure\Http\Controllers\PersonalController;

Route::prefix('personal')->middleware('auth:api')->group(function () {
    Route::get('/buscar', [PersonalController::class, 'search']);
    Route::get('/{id}/estadisticas', [PersonalController::class, 'getEstadisticas']);
    Route::get('/{id}/ingresos', [PersonalController::class, 'getIngresosInvolucrados']);
    Route::get('/', [PersonalController::class, 'index']);
    Route::post('/', [PersonalController::class, 'store']);
    Route::put('/{id}', [PersonalController::class, 'update']);
    Route::delete('/{id}', [PersonalController::class, 'destroy']);
});
