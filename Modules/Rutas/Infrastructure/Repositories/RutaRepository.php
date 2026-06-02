<?php

namespace Modules\Rutas\Infrastructure\Repositories;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\Rutas\Infrastructure\Models\Ruta;
use Exception;

class RutaRepository implements RutaRepositoryInterface
{
    public function crear(array $data)
    {
        return Ruta::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $ruta = $this->obtenerPorId($id);
        if (!$ruta) {
            throw new Exception("Ruta no encontrada", 404);
        }
        $ruta->update($data);
        return $ruta;
    }

    public function eliminar(int $id)
    {
        $ruta = $this->obtenerPorId($id);
        if (!$ruta) {
            throw new Exception("Ruta no encontrada", 404);
        }
        $ruta->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Ruta::with(['personal', 'visitas.paciente'])->find($id);
    }

    public function listar()
    {
        return Ruta::with(['personal', 'visitas.paciente'])->get();
    }

    public function existeRutaParaPersonalEnFecha(int $idPersonal, string $fecha): bool
    {
        return Ruta::where('id_personal', $idPersonal)
            ->where('fecha_ruta', $fecha)
            ->exists();
    }
}
