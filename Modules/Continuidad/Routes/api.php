<?php

use Illuminate\Support\Facades\Route;
use Modules\Continuidad\Infrastructure\Http\Controllers\ContinuidadController;

Route::prefix('continuidad')->middleware('auth:api')->group(function () {
    Route::get('/alertas', [ContinuidadController::class, 'obtenerAlertas']);
});
