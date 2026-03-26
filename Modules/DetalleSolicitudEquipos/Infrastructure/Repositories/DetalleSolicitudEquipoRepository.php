<?php

namespace Modules\DetalleSolicitudEquipos\Infrastructure\Repositories;

use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;
use Modules\DetalleSolicitudEquipos\Infrastructure\Models\DetalleSolicitudEquipo;
use Exception;

class DetalleSolicitudEquipoRepository implements DetalleSolicitudEquipoRepositoryInterface
{
    public function crear(array $data)
    {
        return DetalleSolicitudEquipo::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $detalle = $this->obtenerPorId($id);
        if (!$detalle) {
            throw new Exception("Detalle de solicitud de equipo no encontrado", 404);
        }
        $detalle->update($data);
        return $detalle;
    }

    public function eliminar(int $id)
    {
        $detalle = $this->obtenerPorId($id);
        if (!$detalle) {
            throw new Exception("Detalle de solicitud de equipo no encontrado", 404);
        }
        $detalle->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return DetalleSolicitudEquipo::find($id);
    }

    public function listar()
    {
        return DetalleSolicitudEquipo::all();
    }
}
