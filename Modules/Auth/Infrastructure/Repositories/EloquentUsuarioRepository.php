<?php

namespace Modules\Auth\Infrastructure\Repositories;

use Modules\Auth\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Auth\Domain\Entities\UsuarioEntity;
use Modules\Auth\Infrastructure\Models\Usuario as UsuarioModel;

/**
 * Implementación concreta del repositorio usando Eloquent.
 * Mapea el modelo Eloquent → entidad de dominio.
 */
class EloquentUsuarioRepository implements UsuarioRepositoryInterface
{
    public function findByEmail(string $email): ?UsuarioEntity
    {
        $model = UsuarioModel::with('rol')
            ->where('email', $email)
            ->first();

        return $model ? $this->mapear($model) : null;
    }

    public function findById(int $idUsuario): ?UsuarioEntity
    {
        $model = UsuarioModel::with('rol')->find($idUsuario);

        return $model ? $this->mapear($model) : null;
    }

    private function mapear(UsuarioModel $model): UsuarioEntity
    {
        return new UsuarioEntity(
            idUsuario:      $model->id_usuario,
            nombreCompleto: $model->nombre_completo,
            email:          $model->email,
            idRol:          $model->id_rol,
            rolNombre:      $model->rol?->nombre,
            estado:         $model->estado,
        );
    }
}
