<?php

namespace Modules\Usuarios\Application\UseCases;

use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Exception;

class DesactivarUsuario
{
    private $repository;

    public function __construct(UsuarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $idUsuario)
    {
        if (empty($idUsuario)) {
            throw new Exception("El ID de usuario es requerido para desactivarlo");
        }

        return $this->repository->desactivar($idUsuario);
    }
}
