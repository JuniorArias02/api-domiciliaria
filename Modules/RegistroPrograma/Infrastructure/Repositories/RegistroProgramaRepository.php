<?php

namespace Modules\RegistroPrograma\Infrastructure\Repositories;

use Modules\RegistroPrograma\Domain\Contracts\RegistroProgramaRepositoryInterface;
use Modules\Pacientes\Infrastructure\Models\Paciente;
use Modules\Ingresos\Infrastructure\Models\Ingreso;
use Modules\OrdenesMedicas\Infrastructure\Models\OrdenMedica;

class RegistroProgramaRepository implements RegistroProgramaRepositoryInterface
{
    public function obtenerPacientes(int $porPagina = 30, int $pagina = 1, array $filtros = [])
    {
        $query = Paciente::query()
            ->join('aseguradoras as a', 'a.id_aseguradora', '=', 'pacientes.id_aseguradora')
            ->select([
                'pacientes.id_paciente',
                'pacientes.nombre_completo',
                'pacientes.regimen',
                'a.nombre AS nombre_aseguradora'
            ])
            ->selectRaw("CONCAT(pacientes.tipo_documento, ' ', pacientes.identificacion) AS identificacion")
            ->selectRaw("TIMESTAMPDIFF(YEAR, pacientes.fecha_nacimiento, CURDATE()) AS edad");

        // Filtro por nombre completo (búsqueda parcial)
        if (!empty($filtros['nombre_completo'])) {
            $query->where('pacientes.nombre_completo', 'like', '%' . $filtros['nombre_completo'] . '%');
        }

        // Filtro por identificación (búsqueda parcial)
        if (!empty($filtros['identificacion'])) {
            $query->where('pacientes.identificacion', 'like', '%' . $filtros['identificacion'] . '%');
        }

        // Filtro relacional en ingresos (ingreso y autorización)
        if (!empty($filtros['ingreso']) || !empty($filtros['autorizacion'])) {
            $query->whereHas('ingresos', function ($q) use ($filtros) {
                if (!empty($filtros['ingreso'])) {
                    $q->where('ingreso', 'like', '%' . $filtros['ingreso'] . '%');
                }
                if (!empty($filtros['autorizacion'])) {
                    $q->where('autorizacion', 'like', '%' . $filtros['autorizacion'] . '%');
                }
            });
        }

        return $query->paginate(perPage: $porPagina, page: $pagina);
    }

    public function obtenerAutorizacionesPorPaciente(int $idPaciente)
    {
        return Ingreso::query()
            ->leftJoin('ordenes_medicas', 'ingresos.id_ingreso', '=', 'ordenes_medicas.id_ingreso')
            ->where('ingresos.id_paciente', $idPaciente)
            ->select([
                'ingresos.ingreso',
                'ingresos.fecha_ingreso',
                'ingresos.autorizacion',
                'ordenes_medicas.estado'
            ])
            ->orderBy('ingresos.fecha_ingreso', 'desc')
            ->get();
    }

    public function obtenerOrdenMedicaPorIngreso($ingreso)
    {
        $ingresoObj = Ingreso::where('ingreso', $ingreso)->first();
        if (!$ingresoObj) {
            throw new \Exception("Ingreso no encontrado", 404);
        }

        $ordenes = OrdenMedica::with([
            'servicios.servicio',
            'servicios.profesional',
            'servicios.visitas'
        ])
        ->where('id_ingreso', $ingresoObj->id_ingreso)
        ->get();

        foreach ($ordenes as $orden) {
            foreach ($orden->servicios as $servicio) {
                // Determine if a visit has been made/realized
                $servicio->visita_realizada = $servicio->visitas->contains(function ($visita) {
                    return $visita->fecha_realizada !== null || in_array($visita->estado, ['COMPLETADA', 'REALIZADA']);
                });
            }
        }

        return $ordenes;
    }
}
