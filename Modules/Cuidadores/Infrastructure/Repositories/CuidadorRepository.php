<?php

namespace Modules\Cuidadores\Infrastructure\Repositories;

use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;
use Modules\Cuidadores\Infrastructure\Models\Cuidador;
use Exception;

class CuidadorRepository implements CuidadorRepositoryInterface
{
    public function crear(array $data)
    {
        return Cuidador::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $cuidador = $this->obtenerPorId($id);
        if (!$cuidador) {
            throw new Exception("Cuidador no encontrado", 404);
        }
        $cuidador->update($data);
        return $cuidador;
    }

    public function eliminar(int $id)
    {
        $cuidador = $this->obtenerPorId($id);
        if (!$cuidador) {
            throw new Exception("Cuidador no encontrado", 404);
        }
        $cuidador->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Cuidador::find($id);
    }

    public function listar()
    {
        return Cuidador::all();
    }
}
