<?php

namespace Modules\Laboratorios\Infrastructure\Repositories;

use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;
use Modules\Laboratorios\Infrastructure\Models\Laboratorio;
use Exception;

class LaboratorioRepository implements LaboratorioRepositoryInterface
{
    public function crear(array $data)
    {
        return Laboratorio::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $laboratorio = $this->obtenerPorId($id);
        if (!$laboratorio) {
            throw new Exception("Laboratorio no encontrado", 404);
        }
        $laboratorio->update($data);
        return $laboratorio;
    }

    public function eliminar(int $id)
    {
        $laboratorio = $this->obtenerPorId($id);
        if (!$laboratorio) {
            throw new Exception("Laboratorio no encontrado", 404);
        }
        $laboratorio->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Laboratorio::find($id);
    }

    public function listar()
    {
        return Laboratorio::all();
    }
}
