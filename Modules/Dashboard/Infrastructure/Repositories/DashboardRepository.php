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
            'equipos_en_uso' => DB::table('solicitudes_equipos')->where('estado', 'ENTREGADA')->count(),
            'tutelas_activas' => DB::table('tutelas')
                ->where('es_permanente', 1)
                ->orWhere(function ($query) {
                    $query->where('es_permanente', 0)
                          ->whereRaw('DATE_ADD(fecha_tutela, INTERVAL duracion_dias DAY) >= ?', [Carbon::today()]);
                })->count(),
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
            'visitas_especialidad' => DB::table('visitas_domiciliarias as v')
                ->join('especialidades as e', 'v.id_especialidad', '=', 'e.id_especialidad')
                ->select('e.nombre as especialidad', DB::raw('COUNT(v.id_visita) as total_visitas'))
                ->groupBy('e.id_especialidad', 'e.nombre')
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
        return [
            'estado_solicitudes' => DB::table('solicitudes_equipos')
                ->select('estado', DB::raw('COUNT(*) as cantidad_solicitudes'))
                ->groupBy('estado')
                ->get(),
            'modalidad_equipos' => DB::table('solicitudes_equipos')
                ->whereNotNull('modalidad')
                ->select('modalidad', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('modalidad')
                ->get(),
            'laboratorios_pendientes' => DB::table('laboratorios as l')
                ->join('pacientes as p', 'l.id_paciente', '=', 'p.id_paciente')
                ->where('l.estado', 'PENDIENTE')
                ->where('l.fecha_toma_programada', '<', Carbon::now())
                ->select('l.id_laboratorio', 'p.nombre_completo as paciente', 'l.fecha_toma_programada')
                ->orderBy('l.fecha_toma_programada', 'asc')
                ->get()
        ];
    }

    public function obtenerControlCalidad(): array
    {
        return [
            'promedio_dias_entrega' => DB::table('solicitudes_equipos')
                ->where('estado', 'ENTREGADA')
                ->whereNotNull('fecha_entrega')
                ->avg(DB::raw('DATEDIFF(fecha_entrega, fecha_solicitud)')),
            'efectividad_laboratorios' => DB::table('laboratorios')
                ->whereIn('estado', ['REALIZADO', 'PROGRAMADO'])
                ->select(
                    DB::raw('COUNT(CASE WHEN confirmacion_toma = 1 THEN 1 END) as laboratorios_confirmados'),
                    DB::raw('COUNT(*) as total_laboratorios'),
                    DB::raw('(COUNT(CASE WHEN confirmacion_toma = 1 THEN 1 END) / COUNT(*)) * 100 as porcentaje_efectividad')
                )->first(),
            'demanda_telexperticia' => DB::table('telexperticias as t')
                ->join('especialidades as e', 't.id_especialidad', '=', 'e.id_especialidad')
                ->where('t.fecha_solicitud', '>=', Carbon::now()->subMonths(6))
                ->select(DB::raw("DATE_FORMAT(t.fecha_solicitud, '%Y-%m') as mes"), 'e.nombre as especialidad', DB::raw('COUNT(*) as total_solicitudes'))
                ->groupBy('mes', 'e.nombre')
                ->orderByDesc('mes')
                ->orderByDesc('total_solicitudes')
                ->get()
        ];
    }
}
