<?php

namespace Modules\Especialidades\Infrastructure\Repositories;

use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;
use Modules\Especialidades\Infrastructure\Models\Especialidad;
use Exception;

class EspecialidadRepository implements EspecialidadRepositoryInterface
{
    public function crear(array $data)
    {
        return Especialidad::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $especialidad = $this->obtenerPorId($id);
        if (!$especialidad) {
            throw new Exception("Especialidad no encontrada", 404);
        }
        $especialidad->update($data);
        return $especialidad;
    }

    public function eliminar(int $id)
    {
        $especialidad = $this->obtenerPorId($id);
        if (!$especialidad) {
            throw new Exception("Especialidad no encontrada", 404);
        }
        $especialidad->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Especialidad::find($id);
    }

    public function listar()
    {
        return Especialidad::all();
    }
}
