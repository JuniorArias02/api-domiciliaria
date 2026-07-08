<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateway\Infrastructure\Http\Controllers\GatewayController;

Route::prefix('gateway')->middleware('auth:api')->group(function () {
    Route::get('/ingresos/{ingreso}/detalles', [GatewayController::class, 'obtenerDetalleIngreso']);
});
