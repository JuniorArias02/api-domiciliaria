<?php

namespace Modules\Personal\Infrastructure\Repositories;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\Personal\Infrastructure\Models\Personal;
use Exception;

class PersonalRepository implements PersonalRepositoryInterface
{
    public function crear(array $data)
    {
        return Personal::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $personal = $this->obtenerPorId($id);
        if (!$personal) {
            throw new Exception("Personal no encontrado", 404);
        }
        $personal->update($data);
        return $personal;
    }

    public function eliminar(int $id)
    {
        $personal = $this->obtenerPorId($id);
        if (!$personal) {
            throw new Exception("Personal no encontrado", 404);
        }
        $personal->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Personal::find($id);
    }

    public function listar()
    {
        return Personal::all();
    }

    public function buscar(string $query, int $limit = 5)
    {
        return Personal::where('nombre_completo', 'like', "%{$query}%")
            ->orWhere('numero_documento', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    public function obtenerEstadisticasCumplimiento(int $id)
    {
        // 1. Diagrama circular: Asistió vs No Asistió
        // Asumiendo que 'COMPLETADA' es asistió, y otros estados pueden indicar que no asistió,
        // o simplemente agrupamos por estado.
        $visitasPorEstado = \Illuminate\Support\Facades\DB::table('visitas_domiciliarias')
            ->where('id_personal', $id)
            ->select('estado', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->get();

        // 2. Visitas por mes
        $visitasPorMes = \Illuminate\Support\Facades\DB::table('visitas_domiciliarias')
            ->where('id_personal', $id)
            ->select(
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(fecha_programada, '%Y-%m') as mes"),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as cantidad')
            )
            ->whereNotNull('fecha_programada')
            ->groupBy('mes')
            ->orderBy('mes', 'desc')
            ->get();

        // 3. Número total de visitas de cada paciente
        $visitasPorPaciente = \Illuminate\Support\Facades\DB::table('visitas_domiciliarias as v')
            ->join('pacientes as p', 'v.id_paciente', '=', 'p.id_paciente')
            ->where('v.id_personal', $id)
            ->select('p.id_paciente', 'p.nombre_completo', \Illuminate\Support\Facades\DB::raw('COUNT(v.id_visita) as total_visitas'))
            ->groupBy('p.id_paciente', 'p.nombre_completo')
            ->orderBy('total_visitas', 'desc')
            ->get();

        return [
            'cumplimiento_estados' => $visitasPorEstado,
            'visitas_por_mes' => $visitasPorMes,
            'visitas_por_paciente' => $visitasPorPaciente
        ];
    }

    public function obtenerIngresosInvolucrados(int $id)
    {
        // Buscar todos los ingresos asociados a las órdenes médicas donde el profesional tiene órdenes de servicio asignadas
        return \Illuminate\Support\Facades\DB::table('ingresos')
            ->join('ordenes_medicas', 'ingresos.id_ingreso', '=', 'ordenes_medicas.id_ingreso')
            ->join('ordenes_servicios', 'ordenes_medicas.id_orden', '=', 'ordenes_servicios.id_orden')
            ->join('pacientes', 'ingresos.id_paciente', '=', 'pacientes.id_paciente')
            ->where('ordenes_servicios.id_profesional_asignado', $id)
            ->select(
                'ingresos.id_ingreso',
                'ingresos.ingreso',
                'ingresos.autorizacion',
                'ingresos.fecha_ingreso',
                'pacientes.id_paciente',
                'pacientes.nombre_completo as nombre_paciente',
                'pacientes.identificacion as identificacion_paciente',
                'pacientes.tipo_documento',
                'pacientes.estado as estado_paciente'
            )
            ->distinct()
            ->orderBy('ingresos.fecha_ingreso', 'desc')
            ->get();
    }
}
