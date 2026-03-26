<?php

namespace Modules\Cargos\Infrastructure\Repositories;

use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;
use Modules\Cargos\Infrastructure\Models\Cargo;
use Exception;

class CargosRepository implements CargosRepositoryInterface
{
    public function crear(array $data)
    {
        return Cargo::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $cargo = $this->obtenerPorId($id);
        if (!$cargo) {
            throw new Exception("Cargo no encontrado", 404);
        }
        $cargo->update($data);
        return $cargo;
    }

    public function eliminar(int $id)
    {
        $cargo = $this->obtenerPorId($id);
        if (!$cargo) {
            throw new Exception("Cargo no encontrado", 404);
        }
        $cargo->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Cargo::find($id);
    }

    public function listar()
    {
        return Cargo::all();
    }
}
