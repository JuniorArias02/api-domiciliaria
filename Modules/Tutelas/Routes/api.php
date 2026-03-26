<?php

use Illuminate\Support\Facades\Route;
use Modules\Tutelas\Infrastructure\Http\Controllers\TutelaController;

Route::prefix('tutelas')->middleware('auth:api')->group(function () {
    Route::get('/', [TutelaController::class, 'index']);
    Route::post('/', [TutelaController::class, 'store']);
    Route::put('/{id}', [TutelaController::class, 'update']);
    Route::delete('/{id}', [TutelaController::class, 'destroy']);
});
