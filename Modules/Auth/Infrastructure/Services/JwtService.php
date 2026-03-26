<?php

namespace Modules\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Auth;

/**
 * JwtService — Servicio de infraestructura para operaciones JWT.
 *
 * Aísla el uso de tymon/jwt-auth en un solo lugar.
 * Si en el futuro cambia la librería, solo se modifica este archivo.
 */
class JwtService
{
    /**
     * Intenta autenticar con las credenciales dadas.
     * Retorna el token si son correctas, null si no.
     */
    public function intentarLogin(string $email, string $password): ?string
    {
        $token = Auth::attempt([
            'email'    => $email,
            'password' => $password,
        ]);

        return $token ?: null;
    }

    /** Invalida el token del usuario actualmente autenticado. */
    public function invalidar(): void
    {
        Auth::logout();
    }

    /** Renueva el token actual y retorna el nuevo. */
    public function refrescar(): string
    {
        return Auth::refresh();
    }

    /** Tiempo de vida del token en segundos. */
    public function ttl(): int
    {
        return Auth::factory()->getTTL() * 60;
    }
}
