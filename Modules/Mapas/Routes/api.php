<?php

use Illuminate\Support\Facades\Route;
use Modules\Mapas\Infrastructure\Http\Controllers\MapaController;

Route::prefix('mapas/pacientes')->middleware('auth:api')->group(function () {
    Route::get('/puntos', [MapaController::class, 'getMarkers']);
    Route::get('/detalle/{id}', [MapaController::class, 'getDetail']);
    Route::get('/puntos/todos', [MapaController::class, 'getAllMarkers']);
    Route::get('/{id}/ordenes', [MapaController::class, 'getOrdenesPaciente']);
});

Route::prefix('mapas')->middleware('auth:api')->group(function () {
    Route::get('/rutas-visitas', [MapaController::class, 'getRutaVisitas']);
    Route::get('/rutas-optimizadas', [MapaController::class, 'getRutasOptimizadas']);
    Route::get('/comunas/{id}/pacientes', [MapaController::class, 'getPacientesPorComuna']);
});
