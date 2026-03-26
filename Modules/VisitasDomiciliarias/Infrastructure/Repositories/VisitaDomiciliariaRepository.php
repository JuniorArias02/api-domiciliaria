<?php

namespace Modules\VisitasDomiciliarias\Infrastructure\Repositories;

use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria;
use Exception;

class VisitaDomiciliariaRepository implements VisitaDomiciliariaRepositoryInterface
{
    public function crear(array $data)
    {
        return VisitaDomiciliaria::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $visita = $this->obtenerPorId($id);
        if (!$visita) {
            throw new Exception("Visita domiciliaria no encontrada", 404);
        }
        $visita->update($data);
        return $visita;
    }

    public function eliminar(int $id)
    {
        $visita = $this->obtenerPorId($id);
        if (!$visita) {
            throw new Exception("Visita domiciliaria no encontrada", 404);
        }
        $visita->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return VisitaDomiciliaria::find($id);
    }

    public function listar()
    {
        return VisitaDomiciliaria::all();
    }
}
