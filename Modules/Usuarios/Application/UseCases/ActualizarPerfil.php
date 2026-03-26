<?php

namespace Modules\Usuarios\Application\UseCases;

use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;

class ActualizarPerfil
{
    private $repository;

    public function __construct(UsuarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data)
    {
        $usuarioId = Auth::id();

        if (empty($usuarioId)) {
            throw new Exception("No hay usuario autenticado.");
        }

        // Evitamos que puedan actualizar la contraseña o ID por estè entrypoint
        unset($data['password_hash']);
        unset($data['id_usuario']);

        if (empty($data)) {
            throw new Exception("No hay datos para actualizar.");
        }

        return $this->repository->actualizar($usuarioId, $data);
    }
}
