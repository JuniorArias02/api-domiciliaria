<?php

namespace Modules\Mapas\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class MapaRepository implements MapaRepositoryInterface
{
    /**
     * Devuelve solo marcadores (id, lat, lng, nombre) filtrados.
     * Limitamos a 5000 puntos para no saturar al frontend si no hay filtros.
     */
    public function obtenerPuntosPacientes(array $filtros)
    {
        $query = DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'nombre_completo', 'estado', 'id_comuna');

        // B. Filtros Geo-espaciales
        if (!empty($filtros['id_comuna'])) {
            $query->where('pacientes.id_comuna', $filtros['id_comuna']);
        }

        if (!empty($filtros['id_zona'])) {
            $query->join('comunas', 'pacientes.id_comuna', '=', 'comunas.id_comuna')
                  ->where('comunas.id_zona', $filtros['id_zona']);
        }

        if (!empty($filtros['id_aseguradora'])) {
            $query->where('id_aseguradora', $filtros['id_aseguradora']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('pacientes.estado', $filtros['estado']);
        }

        // Paginación dinámica de 500 pacientes por defecto para no saturar.
        $perPage = $filtros['per_page'] ?? 500;
        return $query->orderBy('pacientes.created_at', 'DESC')->paginate((int)$perPage);
    }

    /**
     * Carga el detalle completo de un marcador específico.
     */
    public function obtenerDetallePaciente(int $id_paciente)
    {
        $paciente = DB::table('pacientes')
            ->leftJoin('aseguradoras', 'pacientes.id_aseguradora', '=', 'aseguradoras.id_aseguradora')
            ->leftJoin('barrios', 'pacientes.id_barrio', '=', 'barrios.id_barrio')
            ->select('pacientes.*', 'aseguradoras.nombre as nombre_aseguradora', 'barrios.nombre as nombre_barrio')
            ->where('id_paciente', $id_paciente)
            ->first();

        if (!$paciente) return null;

        // Ultima Visita y Servicio asociado
        $ultimaVisita = DB::table('visitas_domiciliarias')
            ->leftJoin('servicios', 'visitas_domiciliarias.id_servicio', '=', 'servicios.id_servicio')
            ->leftJoin('personal', 'visitas_domiciliarias.id_personal', '=', 'personal.id_personal')
            ->where('visitas_domiciliarias.id_paciente', $id_paciente)
            ->orderBy('fecha_realizada', 'DESC')
            ->select(
                'visitas_domiciliarias.fecha_realizada', 
                'visitas_domiciliarias.estado as estado_visita',
                'servicios.nombre_servicio',
                'personal.nombre_completo as nombre_profesional'
            )
            ->first();

        // Diagnósticos de este paciente
        $diagnosticos = DB::table('paciente_diagnosticos')
            ->leftJoin('diagnosticos_cie10', 'paciente_diagnosticos.codigo_cie10', '=', 'diagnosticos_cie10.codigo')
            ->where('id_paciente', $id_paciente)
            ->select('diagnosticos_cie10.codigo', 'diagnosticos_cie10.descripcion', 'paciente_diagnosticos.es_principal')
            ->get();

        return [
            'paciente'      => $paciente,
            'ultima_visita' => $ultimaVisita,
            'diagnosticos'  => $diagnosticos
        ];
    }

    /**
     * Obtiene la ruta de visitas (coordenadas de pacientes) organizadas por fecha.
     */
    public function obtenerRutaVisitas(array $filtros)
    {
        $query = DB::table('visitas_domiciliarias')
            ->join('pacientes', 'visitas_domiciliarias.id_paciente', '=', 'pacientes.id_paciente')
            ->leftJoin('personal', 'visitas_domiciliarias.id_personal', '=', 'personal.id_personal')
            ->select(
                'visitas_domiciliarias.id_visita',
                'visitas_domiciliarias.fecha_realizada',
                'visitas_domiciliarias.estado',
                'pacientes.id_paciente',
                'pacientes.nombre_completo as nombre_paciente',
                'pacientes.latitud',
                'pacientes.longitud',
                'pacientes.direccion',
                'personal.id_personal',
                'personal.nombre_completo as nombre_profesional'
            )
            ->whereNotNull('pacientes.latitud')
            ->whereNotNull('pacientes.longitud')
            ->whereNotNull('visitas_domiciliarias.fecha_realizada');

        if (!empty($filtros['id_profesional'])) {
            $query->where('visitas_domiciliarias.id_personal', $filtros['id_profesional']);
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query->whereDate('visitas_domiciliarias.fecha_realizada', '>=', $filtros['fecha_inicio'])
                  ->whereDate('visitas_domiciliarias.fecha_realizada', '<=', $filtros['fecha_fin']);
        } elseif (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('visitas_domiciliarias.fecha_realizada', '=', $filtros['fecha_inicio']);
        }

        // Ordenamos por fecha (ASC), priorizando hora cero, y luego hora descendente (más reciente primero)
        $query->orderBy(DB::raw('DATE(visitas_domiciliarias.fecha_realizada)'), 'ASC')
              ->orderBy(DB::raw('TIME(visitas_domiciliarias.fecha_realizada) = "00:00:00"'), 'DESC')
              ->orderBy(DB::raw('TIME(visitas_domiciliarias.fecha_realizada)'), 'DESC');

        // Paginación dinámica (200 pacientes por defecto)
        $perPage = $filtros['per_page'] ?? 200;
        $paginador = $query->paginate((int)$perPage);

        // Track diario para reiniciar el contador de orden_visita
        $dailyCounter = [];

        // Truncar fecha y asignar orden secuencial por día
        $paginador->getCollection()->transform(function ($visita) use (&$dailyCounter) {
            $fullDate = $visita->fecha_realizada;
            $dateOnly = date('Y-m-d', strtotime($fullDate));

            if (!isset($dailyCounter[$dateOnly])) {
                $dailyCounter[$dateOnly] = 1;
            } else {
                $dailyCounter[$dateOnly]++;
            }

            $visita->orden_visita = $dailyCounter[$dateOnly];
            $visita->fecha_realizada = $dateOnly;
            
            return $visita;
        });

        return $paginador;
    }

    /**
     * Obtiene las órdenes médicas, el paciente y el profesional vinculado basado en un id_paciente.
     */
    public function obtenerOrdenesPaciente(int $id_paciente)
    {
        $paciente = DB::table('pacientes')
            ->select('id_paciente', 'identificacion', 'nombre_completo', 'telefono', 'direccion')
            ->where('id_paciente', $id_paciente)
            ->first();

        if (!$paciente) {
            return null;
        }

        $ordenes = DB::table('ordenes_medicas')
            ->leftJoin('personal', 'ordenes_medicas.id_personal_ordena', '=', 'personal.id_personal')
            ->leftJoin('especialidades', 'ordenes_medicas.id_especialidad', '=', 'especialidades.id_especialidad')
            ->where('ordenes_medicas.id_paciente', $id_paciente)
            ->select(
                'ordenes_medicas.id_orden',
                'ordenes_medicas.fecha_orden',
                'ordenes_medicas.numero_sesiones',
                'ordenes_medicas.frecuencia_dias',
                'ordenes_medicas.estado as estado_orden',
                'personal.id_personal as id_profesional',
                'personal.nombre_completo as nombre_profesional',
                'especialidades.nombre as especialidad'
            )
            ->orderBy('ordenes_medicas.fecha_orden', 'DESC')
            ->get();

        return [
            'paciente' => $paciente,
            'ordenes'  => $ordenes
        ];
    }

    /**
     * Obtiene los pacientes filtrados por comuna con datos geográficos básicos.
     */
    public function obtenerPacientesPorComuna(int $id_comuna)
    {
        return DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'url_google_maps', 'identificacion', 'nombre_completo')
            ->where('id_comuna', $id_comuna)
            ->get();
    }

    /**
     * Obtiene todos los marcadores livianos sin paginación.
     */
    public function obtenerTodosLosPuntos(array $filtros)
    {
        $query = DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'nombre_completo', 'estado', 'id_comuna');

        if (!empty($filtros['id_comuna'])) {
            $query->where('pacientes.id_comuna', $filtros['id_comuna']);
        }

        if (!empty($filtros['id_zona'])) {
            $query->join('comunas', 'pacientes.id_comuna', '=', 'comunas.id_comuna')
                  ->where('comunas.id_zona', $filtros['id_zona']);
        }

        if (!empty($filtros['id_aseguradora'])) {
            $query->where('id_aseguradora', $filtros['id_aseguradora']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('pacientes.estado', $filtros['estado']);
        }

        return $query->orderBy('pacientes.created_at', 'DESC')->get();
    }

    /**
     * Predice y optimiza las rutas del mes basándose en la cercanía geográfica real (Lat/Long).
     * Utiliza un algoritmo de "Vecino más cercano" para generar la secuencia más lógica
     * y las distribuye solo en días de semana con un cupo de 8 visitas diarias.
     */
    public function optimizarRutasMes(array $filtros)
    {
        $mes = $filtros['mes'] ?? date('m');
        $anio = $filtros['anio'] ?? date('Y');

        // 1. Obtener la última visita de cada paciente
        $ultimaVisita = DB::table('visitas_domiciliarias')
            ->select('id_paciente', DB::raw('MAX(fecha_realizada) as ultima_fecha'), 'id_personal')
            ->where('estado', 'COMPLETADA')
            ->groupBy('id_paciente', 'id_personal');

        // 2. Query base de pacientes con órdenes vigentes
        $query = DB::table('ordenes_medicas as om')
            ->join('pacientes as p', 'om.id_paciente', '=', 'p.id_paciente')
            ->leftJoinSub($ultimaVisita, 'uv', function ($join) {
                $join->on('om.id_paciente', '=', 'uv.id_paciente');
            })
            ->leftJoin('personal as per', 'uv.id_personal', '=', 'per.id_personal')
            ->select(
                'p.id_paciente',
                'p.nombre_completo as nombre_paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.id_comuna',
                'p.id_barrio',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'om.id_orden',
                'om.frecuencia_dias',
                DB::raw("DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY) as fecha_proyectada")
            )
            ->where('om.estado', 'VIGENTE')
            ->whereNotNull('per.id_personal')
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud');

        // Filtro por Mes y Año
        $query->whereRaw("MONTH(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$mes])
              ->whereRaw("YEAR(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$anio]);

        if (!empty($filtros['id_personal'])) {
            $query->where('per.id_personal', $filtros['id_personal']);
        }

        $pendientes = $query->get()->toArray();
        if (empty($pendientes)) return [];

        // 3. Algoritmo de Optimización Geográfica (Vecino más cercano)
        // Agrupamos por profesional primero
        $proyectosFinales = [];
        $visitasPorDia = [];
        $primerDia = "$anio-$mes-01";

        $porProfesional = collect($pendientes)->groupBy('id_personal');

        foreach ($porProfesional as $proId => $pacientesPro) {
            $rutaOrdenada = [];
            $lista = $pacientesPro->values()->toArray();

            // Empezamos por el primer paciente de la lista
            $actual = array_shift($lista);
            $rutaOrdenada[] = $actual;

            while (!empty($lista)) {
                $mejorIndice = null;
                $menorDistancia = INF;

                foreach ($lista as $i => $candidato) {
                    $dist = $this->calcularDistancia(
                        $actual->latitud, $actual->longitud,
                        $candidato->latitud, $candidato->longitud
                    );

                    if ($dist < $menorDistancia) {
                        $menorDistancia = $dist;
                        $mejorIndice = $i;
                    }
                }

                $actual = $lista[$mejorIndice];
                $rutaOrdenada[] = $actual;
                array_splice($lista, $mejorIndice, 1);
            }

            // 4. Asignación a días de semana en bloques de 8
            foreach ($rutaOrdenada as $visita) {
                $fechaAsignada = $this->obtenerPrimerDiaSemanaDisponible($proId, $primerDia, $visitasPorDia);
                
                $visita->fecha_proyectada = $fechaAsignada;
                $visitasPorDia[$proId][$fechaAsignada] = ($visitasPorDia[$proId][$fechaAsignada] ?? 0) + 1;
                $visita->orden_visita = $visitasPorDia[$proId][$fechaAsignada];
                
                $proyectosFinales[] = $visita;
            }
        }

        return $proyectosFinales;
    }

    /**
     * Calcula la distancia en línea recta entre dos coordenadas (muy básico pero útil para ordenamiento)
     */
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        return sqrt(pow($lat1 - $lat2, 2) + pow($lon1 - $lon2, 2));
    }

    /**
     * Busca el primer día entre semana (Lunes-Viernes) que tenga cupo disponible.
     */
    private function obtenerPrimerDiaSemanaDisponible($proId, $fechaInicio, &$visitasPorDia)
    {
        $fecha = $fechaInicio;
        
        while (true) {
            $diaSemana = date('N', strtotime($fecha));
            
            if ($diaSemana >= 6) {
                $fecha = date('Y-m-d', strtotime($fecha . ' next monday'));
                continue;
            }
            
            if (($visitasPorDia[$proId][$fecha] ?? 0) < 8) {
                return $fecha;
            }
            
            $fecha = date('Y-m-d', strtotime($fecha . ' +1 day'));
        }
    }
}
