<?php

namespace Modules\Ingresos\Infrastructure\Repositories;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Modules\Ingresos\Infrastructure\Models\Ingreso;

class IngresoRepository implements IngresoRepositoryInterface
{
    public function listar()
    {
        return Ingreso::all();
    }

    public function crear(array $data)
    {
        return Ingreso::create($data);
    }

    public function obtenerAutorizacionesPorPaciente($idPaciente)
    {
        return Ingreso::where('id_paciente', $idPaciente)
            ->whereNotNull('autorizacion')
            ->select('autorizacion', 'fecha_ingreso', 'ingreso')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();
    }
}
