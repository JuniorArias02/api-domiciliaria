<?php

use Illuminate\Support\Facades\Route;
use Modules\Municipios\Infrastructure\Http\Controllers\MunicipioController;

Route::prefix('municipios')->middleware('auth:api')->group(function () {
    Route::get('/', [MunicipioController::class, 'index']);
});
