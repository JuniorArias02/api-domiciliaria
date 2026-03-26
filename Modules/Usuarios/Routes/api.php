<?php

use Illuminate\Support\Facades\Route;
use Modules\Usuarios\Infrastructure\Http\Controllers\UsuarioController;

Route::prefix('v1/usuarios')->middleware('auth:api')->group(function () {
    Route::post('/', [UsuarioController::class, 'store']); // Crear Usuario
    Route::put('/perfil', [UsuarioController::class, 'updateProfile']); // Actualizar Perfil
    Route::put('/contrasena', [UsuarioController::class, 'updatePassword']); // Actualizar Contraseña
    Route::put('/{id}/desactivar', [UsuarioController::class, 'destroy']); // Desactivar Usuario
});
