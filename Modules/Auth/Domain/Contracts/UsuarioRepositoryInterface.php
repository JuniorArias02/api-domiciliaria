<?php

namespace Modules\Auth\Domain\Contracts;

use Modules\Auth\Domain\Entities\UsuarioEntity;

/**
 * Contrato del repositorio de usuarios.
 * La capa de dominio y aplicación solo conoce esta interfaz.
 */
interface UsuarioRepositoryInterface
{
    public function findByEmail(string $email): ?UsuarioEntity;

    public function findById(int $idUsuario): ?UsuarioEntity;
}
