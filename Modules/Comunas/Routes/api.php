<?php

use Illuminate\Support\Facades\Route;
use Modules\Comunas\Infrastructure\Http\Controllers\ComunaController;

Route::prefix('comunas')->middleware('auth:api')->group(function () {
    Route::get('/', [ComunaController::class, 'index']);
});
