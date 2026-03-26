<?php

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Auth\Infrastructure\Services\AccessLogService;
use Modules\Auth\Infrastructure\Services\JwtService;

/**
 * LoginUseCase — Coordina el flujo de autenticación.
 *
 * Flujo: verificar credenciales → obtener usuario → validar activo → log → retornar
 */
class LoginUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
        private JwtService                 $jwtService,
        private AccessLogService           $logService,
    ) {}

    public function execute(string $email, string $password, array $meta = []): array
    {
        // 1. Verificar credenciales (JWT en infraestructura)
        $token = $this->jwtService->intentarLogin($email, $password);

        if ($token === null) {
            throw new \Exception('Credenciales incorrectas.');
        }

        // 2. Obtener entidad de dominio
        $usuario = $this->usuarioRepo->findByEmail($email);

        // 3. Regla de negocio: usuario debe estar activo
        if (! $usuario->isActivo()) {
            $this->jwtService->invalidar(); // revocar token generado
            throw new \Exception('Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 4. Registrar log de acceso
        $this->logService->registrar($usuario->idUsuario, 'LOGIN', $meta);

        // 5. Retornar resultado
        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtService->ttl(),
            'usuario'    => $usuario,
        ];
    }
}
