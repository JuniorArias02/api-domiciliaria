<?php

namespace Modules\OrdenesServicio\Infrastructure\Repositories;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Modules\OrdenesServicio\Infrastructure\Models\OrdenServicio;

class OrdenServicioRepository implements OrdenServicioRepositoryInterface
{
    public function listar()
    {
        return OrdenServicio::all();
    }

    public function crear(array $data)
    {
        return OrdenServicio::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $ordenServicio = $this->obtenerPorId($id);
        if (!$ordenServicio) {
            throw new \Exception("Orden de servicio no encontrada", 404);
        }
        $ordenServicio->update($data);
        return $ordenServicio;
    }

    public function obtenerPorId(int $id)
    {
        return OrdenServicio::find($id);
    }

    public function obtenerPorAutorizacion(string $autorizacion)
    {
        return OrdenServicio::select('ordenes_servicios.*')
            ->join('ordenes_medicas', 'ordenes_servicios.id_orden', '=', 'ordenes_medicas.id_orden')
            ->join('ingresos', 'ordenes_medicas.id_ingreso', '=', 'ingresos.id_ingreso')
            ->where('ingresos.autorizacion', $autorizacion)
            ->with(['servicio', 'visitas' => function ($query) {
                $query->whereNotIn('estado', ['CANCELADA', 'NO_ATENDIDA']);
            }])
            ->get()
            ->map(function ($orden) {
                $orden->sesiones_realizadas = $orden->visitas->count();
                $orden->sesiones_pendientes = max(0, $orden->numero_sesiones - $orden->sesiones_realizadas);
                return $orden;
            })
            ->filter(function ($orden) {
                return $orden->sesiones_pendientes > 0;
            })->values();
    }

    public function obtenerHistorialPorPaciente(int $idPaciente, ?int $idServicio = null)
    {
        $query = OrdenServicio::select(
            'ordenes_servicios.id_orden_servicio',
            'servicios.nombre_servicio',
            'ingresos.id_ingreso',
            'ingresos.fecha_ingreso'
        )
        ->join('ordenes_medicas', 'ordenes_medicas.id_orden', '=', 'ordenes_servicios.id_orden')
        ->join('ingresos', 'ingresos.id_ingreso', '=', 'ordenes_medicas.id_ingreso')
        ->join('servicios', 'servicios.id_servicio', '=', 'ordenes_servicios.id_servicio')
        ->where('ingresos.id_paciente', $idPaciente);

        if ($idServicio) {
            $query->where('ordenes_servicios.id_servicio', $idServicio);
        }

        return $query->orderBy('ingresos.fecha_ingreso', 'desc')
        ->limit(4)
        ->get();
    }

    public function buscarContinuidades(int $idPaciente, array $filtros)
    {
        $query = OrdenServicio::select(
            'ordenes_servicios.id_orden_servicio',
            'servicios.nombre_servicio',
            'ingresos.id_ingreso',
            'ingresos.autorizacion',
            'ingresos.fecha_ingreso'
        )
        ->join('ordenes_medicas', 'ordenes_medicas.id_orden', '=', 'ordenes_servicios.id_orden')
        ->join('ingresos', 'ingresos.id_ingreso', '=', 'ordenes_medicas.id_ingreso')
        ->join('servicios', 'servicios.id_servicio', '=', 'ordenes_servicios.id_servicio')
        ->where('ingresos.id_paciente', $idPaciente);

        if (isset($filtros['id_servicio'])) {
            $query->where('ordenes_servicios.id_servicio', $filtros['id_servicio']);
        }

        if (isset($filtros['numero_ingreso'])) {
            $query->where('ingresos.id_ingreso', $filtros['numero_ingreso']);
        }

        if (isset($filtros['autorizacion'])) {
            $query->where('ingresos.autorizacion', 'like', '%' . $filtros['autorizacion'] . '%');
        }

        if (isset($filtros['mes_inicio'])) {
            $mesInicio = $filtros['mes_inicio'] . '-01 00:00:00';
            $query->where('ingresos.fecha_ingreso', '>=', $mesInicio);
        }

        if (isset($filtros['mes_fin'])) {
            $mesFin = date('Y-m-t 23:59:59', strtotime($filtros['mes_fin'] . '-01'));
            $query->where('ingresos.fecha_ingreso', '<=', $mesFin);
        }

        return $query->orderBy('ingresos.fecha_ingreso', 'desc')->get();
    }
}

