<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Auth Module Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->name('auth.')->group(function () {

    // Públicas
    Route::post('login', [AuthController::class, 'login'])->name('login');

    // Protegidas con JWT
    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::get('me',       [AuthController::class, 'me'])->name('me');
    });
});
