<?php

namespace Modules\OrdenesMedicas\Infrastructure\Repositories;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Modules\OrdenesMedicas\Infrastructure\Models\OrdenMedica;
use Exception;

class OrdenMedicaRepository implements OrdenMedicaRepositoryInterface
{
    public function crear(array $data)
    {
        return OrdenMedica::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $orden = $this->obtenerPorId($id);
        if (!$orden) {
            throw new Exception("Orden Médica no encontrada", 404);
        }
        $orden->update($data);
        return $orden;
    }

    public function eliminar(int $id)
    {
        $orden = $this->obtenerPorId($id);
        if (!$orden) {
            throw new Exception("Orden Médica no encontrada", 404);
        }
        $orden->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return OrdenMedica::with(['servicios.servicio'])->find($id);
    }

    public function obtenerPorNumeroIngreso(int $numeroIngreso)
    {
        return OrdenMedica::with(['servicios.servicio', 'servicios.profesional'])
            ->whereHas('ingreso', function ($query) use ($numeroIngreso) {
                $query->where('ingreso', $numeroIngreso);
            })
            ->get();
    }

    public function listar()
    {
        return OrdenMedica::all();
    }
}
