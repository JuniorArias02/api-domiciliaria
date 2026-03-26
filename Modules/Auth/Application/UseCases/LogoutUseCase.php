<?php

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Infrastructure\Services\AccessLogService;
use Modules\Auth\Infrastructure\Services\JwtService;

/**
 * LogoutUseCase — Registra el log y revoca el token.
 */
class LogoutUseCase
{
    public function __construct(
        private JwtService       $jwtService,
        private AccessLogService $logService,
    ) {}

    public function execute(int $idUsuario, array $meta = []): void
    {
        // Logear antes de invalidar (para tener registro incluso si algo falla)
        $this->logService->registrar($idUsuario, 'LOGOUT', $meta);
        $this->jwtService->invalidar();
    }
}
