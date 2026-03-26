<?php

namespace Modules\Usuarios\Application\UseCases;

use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ActualizarContrasena
{
    private $repository;

    public function __construct(UsuarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $contrasenaActual, string $nuevaContrasena)
    {
        $usuarioId = Auth::id();

        if (empty($usuarioId)) {
            throw new Exception("No hay usuario autenticado.");
        }

        if (empty($contrasenaActual) || empty($nuevaContrasena)) {
            throw new Exception("Ambas contraseñas, actual y nueva, son requeridas.");
        }

        $usuario = $this->repository->buscarPorId($usuarioId);

        if (!$usuario) {
            throw new Exception("Usuario no encontrado.");
        }

        // Validar contraseña actual
        if (!Hash::check($contrasenaActual, $usuario->password_hash)) {
            throw new Exception("La contraseña actual es incorrecta.");
        }

        $nuevoHash = Hash::make($nuevaContrasena);
        
        return $this->repository->actualizar($usuarioId, [
            'password_hash' => $nuevoHash
        ]);
    }
}
