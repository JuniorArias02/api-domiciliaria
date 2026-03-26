<?php

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Infrastructure\Services\JwtService;

/**
 * RefreshTokenUseCase — Renueva el token JWT del usuario autenticado.
 */
class RefreshTokenUseCase
{
    public function __construct(
        private JwtService $jwtService,
    ) {}

    public function execute(): array
    {
        return [
            'token'      => $this->jwtService->refrescar(),
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtService->ttl(),
        ];
    }
}
