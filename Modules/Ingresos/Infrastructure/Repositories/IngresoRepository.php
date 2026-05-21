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
        return Ingreso::leftJoin('ordenes_medicas', 'ingresos.id_ingreso', '=', 'ordenes_medicas.id_ingreso')
            ->where('ingresos.id_paciente', $idPaciente)
            ->whereNotNull('ingresos.autorizacion')
            ->select('ingresos.autorizacion', 'ingresos.fecha_ingreso', 'ingresos.ingreso', 'ordenes_medicas.estado')
            ->orderBy('ingresos.fecha_ingreso', 'desc')
            ->get();
    }

    public function existeAutorizacion(string $autorizacion): bool
    {
        if (empty(trim($autorizacion))) {
            return false;
        }
        return Ingreso::where('autorizacion', $autorizacion)->exists();
    }

    public function obtenerSiguienteNumeroIngreso(): int
    {
        return (int) (Ingreso::max('ingreso') ?? 0) + 1;
    }
}
