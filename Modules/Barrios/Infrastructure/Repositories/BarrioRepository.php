<?php

namespace Modules\Barrios\Infrastructure\Repositories;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;
use Modules\Barrios\Infrastructure\Models\Barrio;
use Exception;

class BarrioRepository implements BarrioRepositoryInterface
{
    public function crear(array $data)
    {
        return Barrio::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $barrio = $this->obtenerPorId($id);
        if (!$barrio) {
            throw new Exception("Barrio no encontrado", 404);
        }
        $barrio->update($data);
        return $barrio;
    }

    public function eliminar(int $id)
    {
        $barrio = $this->obtenerPorId($id);
        if (!$barrio) {
            throw new Exception("Barrio no encontrado", 404);
        }
        $barrio->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Barrio::find($id);
    }

    public function listar()
    {
        return Barrio::all();
    }
}
