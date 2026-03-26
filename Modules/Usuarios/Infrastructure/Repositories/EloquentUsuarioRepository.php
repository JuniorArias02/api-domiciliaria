<?php

namespace Modules\Usuarios\Infrastructure\Repositories;

use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Usuarios\Infrastructure\Models\Usuario;
use Exception;

class EloquentUsuarioRepository implements UsuarioRepositoryInterface
{
    public function crear(array $data)
    {
        return Usuario::create($data);
    }

    public function buscarPorId(int $idUsuario)
    {
        return Usuario::find($idUsuario);
    }

    public function actualizar(int $idUsuario, array $data)
    {
        $usuario = Usuario::find($idUsuario);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        $usuario->update($data);
        return $usuario;
    }

    public function desactivar(int $idUsuario)
    {
        $usuario = Usuario::find($idUsuario);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        // Asumiendo que estado 0 significa inactivo, basado en el contexto de tu DB o 2.
        // Lo pondremos en 0.
        $usuario->estado = 0;
        $usuario->save();
        
        return $usuario;
    }
}
