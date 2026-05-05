<?php

use Illuminate\Support\Facades\Route;
use Modules\Agenda\Infrastructure\Http\Controllers\AgendaController;

/*
|--------------------------------------------------------------------------
| API Routes for Agenda
|--------------------------------------------------------------------------
*/

Route::prefix('agenda')->middleware('auth:api')->group(function () {
    Route::get('/listado', [AgendaController::class, 'index']);
    Route::get('/listado-detallado', [AgendaController::class, 'listadoDetallado']);
    Route::post('/crear', [AgendaController::class, 'crearAgendaCompleta']);
    Route::post('/crear-masiva', [AgendaController::class, 'crearMasiva']);
});
 