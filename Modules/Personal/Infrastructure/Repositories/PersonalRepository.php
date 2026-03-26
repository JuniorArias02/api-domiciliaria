<?php

namespace Modules\Personal\Infrastructure\Repositories;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\Personal\Infrastructure\Models\Personal;
use Exception;

class PersonalRepository implements PersonalRepositoryInterface
{
    public function crear(array $data)
    {
        return Personal::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $personal = $this->obtenerPorId($id);
        if (!$personal) {
            throw new Exception("Personal no encontrado", 404);
        }
        $personal->update($data);
        return $personal;
    }

    public function eliminar(int $id)
    {
        $personal = $this->obtenerPorId($id);
        if (!$personal) {
            throw new Exception("Personal no encontrado", 404);
        }
        $personal->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Personal::find($id);
    }

    public function listar()
    {
        return Personal::all();
    }
}
