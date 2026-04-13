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
    Route::post('/crear', [AgendaController::class, 'crearAgendaCompleta']);
});
