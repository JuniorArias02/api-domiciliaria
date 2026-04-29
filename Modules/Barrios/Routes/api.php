<?php

use Illuminate\Support\Facades\Route;
use Modules\Barrios\Infrastructure\Http\Controllers\BarrioController;

Route::prefix('barrios')->middleware('auth:api')->group(function () {
    Route::get('/', [BarrioController::class, 'index']);
});
