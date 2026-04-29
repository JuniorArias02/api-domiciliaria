<?php

use Illuminate\Support\Facades\Route;
use Modules\Departamentos\Infrastructure\Http\Controllers\DepartamentoController;

Route::prefix('departamentos')->middleware('auth:api')->group(function () {
    Route::get('/', [DepartamentoController::class, 'index']);
});
