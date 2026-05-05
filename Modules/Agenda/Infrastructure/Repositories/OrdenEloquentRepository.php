<?php

declare(strict_types=1);

namespace Modules\Agenda\Infrastructure\Repositories;

use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrdenEloquentRepository implements OrdenRepositoryInterface
{
    public function crearOrden(array $datosOrden): int
    {
        // Usamos Query Builder o un modelo si lo tienes
        // Asegúrate de que la tabla 'ordenes_medicas' exista.
        return DB::table('ordenes_medicas')->insertGetId($datosOrden);
    }

    public function listarPaginado(array $filtros, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        // 1. Obtener la última visita (fecha) por cada orden_servicio
        $ultimaVisita = DB::table('visitas_domiciliarias')
            ->select('id_orden_servicio', DB::raw('MAX(fecha_realizada) as ultima_visita'), DB::raw('COUNT(CASE WHEN estado = "COMPLETADA" THEN 1 END) as sesiones_completadas'))
            ->groupBy('id_orden_servicio');

        $query = DB::table('ordenes_servicios as os')
            ->join('ordenes_medicas as om', 'os.id_orden', '=', 'om.id_orden')
            ->join('ingresos as i', 'om.id_ingreso', '=', 'i.id_ingreso')
            ->join('pacientes as p', 'i.id_paciente', '=', 'p.id_paciente')
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            ->leftJoin('personal as per', 'os.id_profesional_asignado', '=', 'per.id_personal')
            ->leftJoinSub($ultimaVisita, 'uv', function ($join) {
                $join->on('os.id_orden_servicio', '=', 'uv.id_orden_servicio');
            })
            ->select(
                'os.id_orden_servicio',
                'os.estado as estado_servicio',
                'os.numero_sesiones',
                'os.frecuencia_dias',
                'om.id_orden',
                'om.fecha_orden',
                'om.estado as estado_orden',
                'p.id_paciente',
                'p.identificacion',
                'p.nombre_completo as nombre_paciente',
                'p.telefono',
                'p.direccion',
                'p.latitud',
                'p.longitud',
                's.id_servicio',
                's.codigo_servicio',
                's.nombre_servicio',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                DB::raw('COALESCE(uv.sesiones_completadas, 0) as sesiones_completadas'),
                'uv.ultima_visita'
            )
            ->where('om.estado', 'VIGENTE')
            ->orderBy('om.fecha_orden', 'desc');

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('p.nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('p.identificacion', 'like', "%{$buscar}%")
                  ->orWhere('s.nombre_servicio', 'like', "%{$buscar}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('os.estado', $filtros['estado']);
        }

        $paginator = $query->paginate($perPage);

        // Calcular la "próxima visita" en la colección
        $paginator->getCollection()->transform(function ($item) {
            $ultima = $item->ultima_visita ? new \Carbon\Carbon($item->ultima_visita) : new \Carbon\Carbon($item->fecha_orden);
            $item->proxima_visita_estimada = $ultima->copy()->addDays($item->frecuencia_dias ?? 0)->format('Y-m-d H:i:s');
            $item->sesiones_restantes = max(0, $item->numero_sesiones - $item->sesiones_completadas);
            return $item;
        });

        return $paginator;
    }

    public function listarAgendasDetalladas(array $filtros, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = DB::table('ordenes_medicas as om')
            ->join('ingresos as i', 'om.id_ingreso', '=', 'i.id_ingreso')
            ->join('pacientes as p', 'i.id_paciente', '=', 'p.id_paciente')
            ->select(
                'om.id_orden',
                'om.fecha_orden',
                'om.observacion',
                'om.estado as estado_orden',
                'i.autorizacion',
                'p.id_paciente',
                'p.identificacion',
                'p.nombre_completo as nombre_paciente',
                'p.telefono',
                'p.direccion'
            )
            ->orderBy('om.fecha_orden', 'desc');

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('p.nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('p.identificacion', 'like', "%{$buscar}%")
                  ->orWhere('i.autorizacion', 'like', "%{$buscar}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('om.estado', $filtros['estado']);
        }

        $paginator = $query->paginate($perPage);

        $ordenIds = $paginator->getCollection()->pluck('id_orden')->toArray();

        if (empty($ordenIds)) {
            return $paginator;
        }

        // Obtener órdenes de servicios
        $servicios = DB::table('ordenes_servicios as os')
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            ->leftJoin('personal as per', 'os.id_profesional_asignado', '=', 'per.id_personal')
            ->select(
                'os.*',
                's.nombre_servicio',
                'per.nombre_completo as nombre_profesional'
            )
            ->whereIn('os.id_orden', $ordenIds)
            ->get();

        $servicioIds = $servicios->pluck('id_orden_servicio')->toArray();

        // Obtener visitas
        $visitas = collect();
        if (!empty($servicioIds)) {
            $visitas = DB::table('visitas_domiciliarias')
                ->select(
                    'id_visita',
                    'id_orden_servicio',
                    'fecha_programada',
                    'fecha_realizada',
                    'estado',
                    'tipo_atencion_ext'
                )
                ->whereIn('id_orden_servicio', $servicioIds)
                ->get()
                ->groupBy('id_orden_servicio');
        }

        $serviciosGrouped = $servicios->groupBy('id_orden');

        $paginator->getCollection()->transform(function ($orden) use ($serviciosGrouped, $visitas) {
            $ordenServicios = $serviciosGrouped->get($orden->id_orden, collect());
            
            $orden->ordenes_servicios = $ordenServicios->map(function ($os) use ($visitas) {
                $os->visitas_domiciliarias = $visitas->get($os->id_orden_servicio, collect());
                return $os;
            });

            return $orden;
        });

        return $paginator;
    }
}
