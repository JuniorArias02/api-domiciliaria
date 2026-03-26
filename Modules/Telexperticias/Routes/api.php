<?php

use Illuminate\Support\Facades\Route;
use Modules\Telexperticias\Infrastructure\Http\Controllers\TelexperticiaController;

Route::prefix('telexperticias')->middleware('auth:api')->group(function () {
    Route::get('/', [TelexperticiaController::class, 'index']);
    Route::post('/', [TelexperticiaController::class, 'store']);
    Route::put('/{id}', [TelexperticiaController::class, 'update']);
    Route::delete('/{id}', [TelexperticiaController::class, 'destroy']);
});
