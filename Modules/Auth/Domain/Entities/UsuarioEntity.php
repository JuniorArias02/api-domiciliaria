<?php

namespace Modules\Auth\Domain\Entities;

/**
 * UsuarioEntity — Entidad de dominio pura (sin dependencias de framework).
 */
final class UsuarioEntity
{
    public function __construct(
        public readonly int     $idUsuario,
        public readonly string  $nombreCompleto,
        public readonly string  $email,
        public readonly int     $idRol,
        public readonly ?string $rolNombre,
        public readonly int     $estado,
    ) {}

    public function isActivo(): bool
    {
        return $this->estado === 1;
    }
}
