<?php

namespace Modules\Zonas\Infrastructure\Repositories;

use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;
use Modules\Zonas\Infrastructure\Models\Zona;
use Exception;

class ZonaRepository implements ZonaRepositoryInterface
{
    public function crear(array $data)
    {
        return Zona::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $zona = $this->obtenerPorId($id);
        if (!$zona) {
            throw new Exception("Zona no encontrada", 404);
        }
        $zona->update($data);
        return $zona;
    }

    public function eliminar(int $id)
    {
        $zona = $this->obtenerPorId($id);
        if (!$zona) {
            throw new Exception("Zona no encontrada", 404);
        }
        $zona->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Zona::find($id);
    }

    public function listar()
    {
        return Zona::all();
    }
}
