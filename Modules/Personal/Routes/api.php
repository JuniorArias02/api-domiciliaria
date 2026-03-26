<?php

use Illuminate\Support\Facades\Route;
use Modules\Personal\Infrastructure\Http\Controllers\PersonalController;

Route::prefix('personal')->middleware('auth:api')->group(function () {
    Route::get('/', [PersonalController::class, 'index']);
    Route::post('/', [PersonalController::class, 'store']);
    Route::put('/{id}', [PersonalController::class, 'update']);
    Route::delete('/{id}', [PersonalController::class, 'destroy']);
});
