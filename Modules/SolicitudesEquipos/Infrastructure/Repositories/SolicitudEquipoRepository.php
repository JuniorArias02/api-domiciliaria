<?php

namespace Modules\SolicitudesEquipos\Infrastructure\Repositories;

use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;
use Modules\SolicitudesEquipos\Infrastructure\Models\SolicitudEquipo;
use Exception;

class SolicitudEquipoRepository implements SolicitudEquipoRepositoryInterface
{
    public function crear(array $data)
    {
        return SolicitudEquipo::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $solicitud = $this->obtenerPorId($id);
        if (!$solicitud) {
            throw new Exception("Solicitud de equipo no encontrada", 404);
        }
        $solicitud->update($data);
        return $solicitud;
    }

    public function eliminar(int $id)
    {
        $solicitud = $this->obtenerPorId($id);
        if (!$solicitud) {
            throw new Exception("Solicitud de equipo no encontrada", 404);
        }
        $solicitud->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return SolicitudEquipo::find($id);
    }

    public function listar()
    {
        return SolicitudEquipo::all();
    }
}
