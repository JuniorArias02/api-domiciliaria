<?php

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Auth\Domain\Entities\UsuarioEntity;

/**
 * MeUseCase — Obtiene los datos del usuario autenticado.
 */
class MeUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {}

    public function execute(int $idUsuario): UsuarioEntity
    {
        $usuario = $this->usuarioRepo->findById($idUsuario);

        if ($usuario === null) {
            throw new \Exception('Usuario no encontrado.');
        }

        return $usuario;
    }
}
