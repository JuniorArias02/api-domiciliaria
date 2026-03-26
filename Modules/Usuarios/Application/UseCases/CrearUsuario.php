<?php

namespace Modules\Usuarios\Application\UseCases;

use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;

class CrearUsuario
{
    private $repository;

    public function __construct(UsuarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre_completo'])) {
            throw new Exception("El nombre completo es requerido");
        }

        if (empty($data['email'])) {
            throw new Exception("El email es requerido");
        }

        if (empty($data['password_hash'])) {
            throw new Exception("La contraseña es requerida");
        }

        if (empty($data['id_rol'])) {
            throw new Exception("El ID del rol es requerido");
        }

        // Hasheamos la contraseña antes de crear
        $data['password_hash'] = Hash::make($data['password_hash']);
        
        // Estado por defecto activo
        $data['estado'] = $data['estado'] ?? 1;

        return $this->repository->crear($data);
    }
}
