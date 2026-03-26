<?php

namespace Modules\Usuarios\Domain\Contracts;

interface UsuarioRepositoryInterface
{
    public function crear(array $data);
    public function buscarPorId(int $idUsuario);
    public function actualizar(int $idUsuario, array $data);
    public function desactivar(int $idUsuario);
}
