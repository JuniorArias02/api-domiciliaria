<?php

namespace Modules\Dashboard\Infrastructure\Repositories;

use Modules\Dashboard\Domain\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function obtenerKpisOperativos(): array
    {
        return [
            'pacientes_activos' => DB::table('pacientes')->where('estado', 'ACTIVO')->count(),
            'visitas_hoy' => DB::table('visitas_domiciliarias')
                ->whereDate('fecha_programada', Carbon::today())
                ->select('estado', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('estado')
                ->get(),
            'ordenes_vigentes' => DB::table('ordenes_medicas')->where('estado', 'VIGENTE')->count(),
            'servicio_mas_solicitado' => DB::table('visitas_domiciliarias as v')
                ->join('ordenes_servicios as os', 'v.id_orden_servicio', '=', 'os.id_orden_servicio')
                ->join('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
                ->select('s.nombre_servicio as servicio', DB::raw('COUNT(v.id_visita) as total_visitas'))
                ->groupBy('s.id_servicio', 's.nombre_servicio')
                ->orderByDesc('total_visitas')
                ->first(),
        ];
    }

    public function obtenerGestionVisitas(): array
    {
        return [
            'efectividad_mes' => DB::table('visitas_domiciliarias')
                ->whereMonth('fecha_programada', Carbon::now()->month)
                ->whereYear('fecha_programada', Carbon::now()->year)
                ->select('estado', DB::raw('COUNT(*) as total_visitas'))
                ->groupBy('estado')
                ->get(),
            'carga_profesionales' => DB::table('visitas_domiciliarias as v')
                ->join('personal as p', 'v.id_personal', '=', 'p.id_personal')
                ->whereMonth('v.fecha_programada', Carbon::now()->month)
                ->whereYear('v.fecha_programada', Carbon::now()->year)
                ->select('p.nombre_completo', DB::raw('COUNT(v.id_visita) as total_visitas_asignadas'))
                ->groupBy('p.id_personal', 'p.nombre_completo')
                ->orderByDesc('total_visitas_asignadas')
                ->limit(10)
                ->get(),
            'visitas_servicio' => DB::table('visitas_domiciliarias as v')
                ->join('ordenes_servicios as os', 'v.id_orden_servicio', '=', 'os.id_orden_servicio')
                ->join('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
                ->select('s.nombre_servicio as servicio', DB::raw('COUNT(v.id_visita) as total_visitas'))
                ->where('v.estado', 'COMPLETADA')
                ->groupBy('s.id_servicio', 's.nombre_servicio')
                ->orderByDesc('total_visitas')
                ->get()
        ];
    }

    public function obtenerDemografia(): array
    {
        return [
            'pacientes_aseguradora' => DB::table('pacientes as p')
                ->join('aseguradoras as a', 'p.id_aseguradora', '=', 'a.id_aseguradora')
                ->where('p.estado', 'ACTIVO')
                ->select('a.nombre as aseguradora', DB::raw('COUNT(p.id_paciente) as total_pacientes'))
                ->groupBy('a.id_aseguradora', 'a.nombre')
                ->orderByDesc('total_pacientes')
                ->get(),
            'piramide_poblacional' => DB::table('pacientes')
                 ->where('estado', 'ACTIVO')
                 ->select('sexo', DB::raw("
                    CASE
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 18 THEN '0-17 años'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 35 THEN '18-35 años'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 36 AND 60 THEN '36-60 años'
                        ELSE 'Mayor de 60 años'
                    END as rango_edad
                 "), DB::raw('COUNT(*) as cantidad'))   
                 ->groupBy('sexo', 'rango_edad')
                 ->orderBy('rango_edad')
                 ->orderBy('sexo')
                 ->get(),
            'top_diagnosticos' => DB::table('paciente_diagnosticos as pd')
                ->join('diagnosticos_cie10 as d', 'pd.codigo_cie10', '=', 'd.codigo')
                ->where('pd.es_principal', 1)
                ->select('d.descripcion', DB::raw('COUNT(pd.id_paciente) as total_casos'))
                ->groupBy('pd.codigo_cie10', 'd.descripcion')
                ->orderByDesc('total_casos')
                ->limit(10)
                ->get(),
        ];
    }

    public function obtenerGestionRecursos(): array
    {
        return []; // Tablas de equipos eliminadas
    }

    public function obtenerControlCalidad(): array
    {
        return []; // Tablas de laboratorios y telexperticias eliminadas
    }
}
