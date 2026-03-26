<?php

use Illuminate\Support\Facades\Route;
use Modules\OrdenesMedicas\Infrastructure\Http\Controllers\OrdenMedicaController;

Route::prefix('ordenes-medicas')->middleware('auth:api')->group(function () {
    Route::get('/', [OrdenMedicaController::class, 'index']);
    Route::post('/', [OrdenMedicaController::class, 'store']);
    Route::put('/{id}', [OrdenMedicaController::class, 'update']);
    Route::delete('/{id}', [OrdenMedicaController::class, 'destroy']);
});
