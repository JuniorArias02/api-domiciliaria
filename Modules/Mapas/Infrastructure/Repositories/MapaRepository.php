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
        // 1. Obtener el ID de la última visita de cada paciente para poder traer los datos de esa visita específica
        $ultimaVisitaId = DB::table('visitas_domiciliarias')
            ->select('id_paciente', DB::raw('MAX(id_visita) as max_id'))
            ->where('estado', 'COMPLETADA')
            ->groupBy('id_paciente');

        $ultimaVisita = DB::table('visitas_domiciliarias as vd')
            ->joinSub($ultimaVisitaId, 'uv_id', function($join) {
                $join->on('vd.id_visita', '=', 'uv_id.max_id');
            })
            ->select('vd.id_paciente', 'vd.fecha_realizada', 'vd.id_personal');

        // 2. Query base de pacientes con su última información (sin importar fecha/año)
        $query = DB::table('pacientes as p')
            ->join('ordenes_medicas as om', 'p.id_paciente', '=', 'om.id_paciente')
            ->leftJoinSub($ultimaVisita, 'uv', function ($join) {
                $join->on('p.id_paciente', '=', 'uv.id_paciente');
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
                'p.telefono',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'om.id_orden',
                'om.frecuencia_dias'
            )
            ->where('om.estado', 'VIGENTE')
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud');

        // Para evitar que un paciente salga 2 veces si tiene 2 órdenes vigentes,
        // agrupamos por paciente (esto es lo que causaba el error de ONLY_FULL_GROUP_BY)
        // La solución es agrupar por todas las columnas que estamos seleccionando.
        $query->groupBy(
            'p.id_paciente',
            'p.nombre_completo',
            'p.latitud',
            'p.longitud',
            'p.direccion',
            'p.id_comuna',
            'p.id_barrio',
            'p.telefono',
            'per.id_personal',
            'per.nombre_completo',
            'om.id_orden',
            'om.frecuencia_dias'
        );

        if (!empty($filtros['id_personal'])) {
            $query->where('per.id_personal', $filtros['id_personal']);
        }

        $pendientes = $query->get()->toArray();
        if (empty($pendientes)) return [];

        // 3. Algoritmo de Optimización Geográfica MEGA GLOBAL (Vecino más cercano)
        $proyectosFinales = [];
        $rutaOrdenada = [];
        $lista = $pendientes; // Lista GLOBAL, sin separar por profesor

        // Proximidad Cercana (Vecino más cercano)
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

        // 4. Fragmentación en grupos de 8 (Bloques lógicos generales)
        $grupos = array_chunk($rutaOrdenada, 8);
        
        foreach ($grupos as $idxGrupo => $grupo) {
            $bloqueRuta = $idxGrupo + 1;

            foreach ($grupo as $idxVisita => $visita) {
                $visita->bloque_ruta = $bloqueRuta;           // A qué conjunto de 8 pertenece
                $visita->orden_en_ruta = $idxVisita + 1;      // 1 al 8
                $visita->orden_global = ($idxGrupo * 8) + ($idxVisita + 1); // Posición absoluta
                
                // Limpieza temporal
                $visita->latitud = (float) $visita->latitud;
                $visita->longitud = (float) $visita->longitud;
                
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
     * Optimiza las rutas del mes basándose estrictamente en el campo orden_mapa de los pacientes.
     * Este método ignora la cercanía geográfica calculada y respeta el orden numérico asignado
     * manualmente a cada paciente en su respectiva comuna.
     */
    public function optimizarRutasMesMetodoOrden(array $filtros)
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
                'p.orden_mapa',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'om.id_orden',
                'om.frecuencia_dias',
                DB::raw("DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY) as fecha_proyectada")
            )
            ->where('om.estado', 'VIGENTE');

        // Filtro por Mes y Año sobre la fecha proyectada
        $query->whereRaw("MONTH(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$mes])
              ->whereRaw("YEAR(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$anio]);

        if (!empty($filtros['id_personal'])) {
            $query->where('per.id_personal', $filtros['id_personal']);
        }

        // ORDENAMIENTO ESTRICTO por orden_mapa
        $query->orderBy('p.orden_mapa', 'ASC');

        $pendientes = $query->get()->toArray();
        if (empty($pendientes)) return [];

        // 3. Distribución en calendario (Días diferentes para órdenes diferentes)
        $proyectosFinales = [];
        $primerDiaMes = "$anio-$mes-01";

        // Agrupamos por profesional para manejar sus calendarios individuales
        $porProfesional = collect($pendientes)->groupBy('id_personal');

        foreach ($porProfesional as $proId => $pacientesPro) {
            // Agrupamos por orden_mapa y ordenamos las llaves
            $gruposPorOrden = $pacientesPro->groupBy('orden_mapa')->sortKeys();
            
            $fechaPuntero = $primerDiaMes;

            foreach ($gruposPorOrden as $ordenValue => $visitasGrupo) {
                // Obtenemos el siguiente día hábil disponible para este grupo
                $fechaAsignada = $this->obtenerSiguienteDiaHabil($fechaPuntero);
                
                $contadorEnDia = 1;
                foreach ($visitasGrupo as $visita) {
                    $visita->fecha_proyectada = $fechaAsignada;
                    // El orden dentro del día sigue siendo secuencial
                    $visita->orden_visita = $contadorEnDia++; 
                    $proyectosFinales[] = $visita;
                }

                // IMPORTANTE: El siguiente número de orden DEBE ir en un día diferente
                $fechaPuntero = date('Y-m-d', strtotime($fechaAsignada . ' +1 day'));
            }
        }

        return $proyectosFinales;
    }

    /**
     * Organiza rutas basadas en proximidad geográfica con un mínimo de 8 pacientes.
     * 1. Filtra por profesional y mes proyectado.
     * 2. Calcula cercanía usando lat/long.
     * 3. Agrupa en bloques de mínimo 8 pacientes.
     */
    public function optimizarRutasMesCercania(array $filtros)
    {
        $mes = $filtros['mes'] ?? date('m');
        $anio = $filtros['anio'] ?? date('Y');
        $idPersonal = $filtros['id_personal'] ?? null;

        if (!$idPersonal) {
            throw new \InvalidArgumentException("Debe seleccionar un profesional médico.");
        }

        // 1. Obtener la última visita de cada paciente (para el cálculo de frecuencia)
        $ultimaVisita = DB::table('visitas_domiciliarias')
            ->select('id_paciente', DB::raw('MAX(fecha_realizada) as ultima_fecha'), 'id_personal')
            ->where('estado', 'COMPLETADA')
            ->groupBy('id_paciente', 'id_personal');

        // 2. Query de pacientes que TIENEN visita en este mes según frecuencia
        $query = DB::table('ordenes_medicas as om')
            ->join('pacientes as p', 'om.id_paciente', '=', 'p.id_paciente')
            ->leftJoinSub($ultimaVisita, 'uv', function ($join) {
                $join->on('om.id_paciente', '=', 'uv.id_paciente');
            })
            ->join('personal as per', function($join) use ($idPersonal) {
                // Filtramos por el profesional vinculado a la última visita o al que se desea asignar
                $join->on('uv.id_personal', '=', 'per.id_personal');
            })
            ->select(
                'p.id_paciente',
                'p.nombre_completo as nombre_paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'om.id_orden',
                'om.frecuencia_dias',
                DB::raw("DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY) as fecha_proyectada")
            )
            ->where('om.estado', 'VIGENTE')
            ->where('per.id_personal', $idPersonal)
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud');

        // Filtro estricto por Mes y Año sobre la fecha proyectada
        $query->whereRaw("MONTH(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$mes])
              ->whereRaw("YEAR(DATE_ADD(COALESCE(uv.ultima_fecha, om.fecha_orden), INTERVAL om.frecuencia_dias DAY)) = ?", [$anio]);

        $pacientesDisponibles = $query->get()->toArray();
        if (empty($pacientesDisponibles)) return [];

        // 3. Generar lista de proximidad geográfica (Vecino más cercano)
        $rutaOrdenada = [];
        $pendientes = $pacientesDisponibles;
        
        // Empezamos con el primero de la lista
        $actual = array_shift($pendientes);
        $rutaOrdenada[] = $actual;

        while (!empty($pendientes)) {
            $mejorIndice = null;
            $menorDistancia = INF;

            foreach ($pendientes as $i => $candidato) {
                $dist = $this->calcularDistancia(
                    $actual->latitud, $actual->longitud,
                    $candidato->latitud, $candidato->longitud
                );

                if ($dist < $menorDistancia) {
                    $menorDistancia = $dist;
                    $mejorIndice = $i;
                }
            }

            $actual = $pendientes[$mejorIndice];
            $rutaOrdenada[] = $actual;
            array_splice($pendientes, $mejorIndice, 1);
        }

        // 4. Construir rutas organizadas con MÍNIMO 8 pacientes
        $rutasFinales = [];
        $totalPacientes = count($rutaOrdenada);
        
        // Si el total es menor a 8, generamos una sola ruta (aunque no cumpla el mínimo, es lo que hay)
        if ($totalPacientes < 8) {
            foreach ($rutaOrdenada as $idx => $p) {
                $p->numero_ruta = 1;
                $p->orden_en_ruta = $idx + 1;
                $rutasFinales[] = $p;
            }
        } else {
            // Dividimos intentando que cada bloque sea de 8. 
            // Si el sobrante es < 8, lo redistribuimos o lo dejamos como la "última gran ruta"
            $chunks = array_chunk($rutaOrdenada, 8);
            
            // Si el último chunk tiene menos de 8, lo fusionamos con el penúltimo para mantener el mínimo
            if (count($chunks) > 1 && count(end($chunks)) < 8) {
                $ultimo = array_pop($chunks);
                $penultimo = array_pop($chunks);
                $fusion = array_merge($penultimo, $ultimo);
                $chunks[] = $fusion;
            }

            foreach ($chunks as $idxRuta => $grupo) {
                foreach ($grupo as $idxVisita => $visita) {
                    $visita->numero_ruta = $idxRuta + 1;
                    $visita->orden_en_ruta = $idxVisita + 1;
                    /** 
                     * IMPORTANTE: No asignamos fecha específica para no ignorar el requerimiento 
                     * de "ignorar días específicos", solo representamos el orden en el mega listado.
                     */
                    $rutasFinales[] = $visita;
                }
            }
        }

        return $rutasFinales;
    }





    public function optimizarRutasMetodosTres(array $filtros)
    {
        // esperando instrucciones
    }

    /**
     * Lógica exacta del Script de Python (metodoUno)
     * Optimiza rutas globales basadas en cercanía y frecuencia.
     */
    public function optimizarRutasGlobales(array $filtros)
    {
        $mes = $filtros['mes'] ?? 4;
        $anio = $filtros['anio'] ?? 2026;

        // 1. Obtener candidatos (Query del script)
        $resultados = DB::table('pacientes as p')
            ->join('ordenes_medicas as om', 'p.id_paciente', '=', 'om.id_paciente')
            ->leftJoin('visitas_domiciliarias as vd', 'p.id_paciente', '=', 'vd.id_paciente')
            ->select(
                'p.id_paciente',
                'p.nombre_completo as paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.telefono',
                'om.frecuencia_dias',
                DB::raw('MAX(vd.fecha_realizada) as ultima_visita')
            )
            ->where('om.estado', 'VIGENTE')
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud')
            ->where('p.latitud', '!=', 0)
            ->groupBy('p.id_paciente', 'om.id_orden')
            ->get();

        $candidatos = [];
        foreach ($resultados as $row) {
            $frecuencia = $row->frecuencia_dias ?: 30;
            
            if ($row->ultima_visita) {
                $ultima = new \DateTime($row->ultima_visita);
            } else {
                // Si no hay visitas previas, asumimos que debe visitarse a principio de mes (Python: ultima = datetime(anio, mes, 1) - timedelta(days=frecuencia))
                $ultima = (new \DateTime("$anio-$mes-01"))->modify("-{$frecuencia} days");
            }

            $proxima = clone $ultima;
            $proxima->modify("+{$frecuencia} days");

            // Solo incluimos si cae en el mes/anio objetivo
            if ((int)$proxima->format('m') == $mes && (int)$proxima->format('Y') == $anio) {
                $candidatos[] = [
                    "id_paciente" => $row->id_paciente,
                    "nombre_paciente" => $row->paciente,
                    "direccion" => $row->direccion,
                    "telefono" => $row->telefono,
                    "latitud" => (float)$row->latitud,
                    "longitud" => (float)$row->longitud,
                    "fecha_proyectada" => $proxima->format('Y-m-d')
                ];
            }
        }

        if (empty($candidatos)) return [];

        // 2. Optimización (Vecino más cercano)
        $ordenados = [];
        $pendientes = $candidatos;
        
        $actual = array_shift($pendientes);
        $ordenados[] = $actual;

        while (!empty($pendientes)) {
            $mejorIndice = 0;
            $distanciaMin = $this->haversine($actual['latitud'], $actual['longitud'], $pendientes[0]['latitud'], $pendientes[0]['longitud']);

            for ($i = 1; $i < count($pendientes); $i++) {
                $dist = $this->haversine($actual['latitud'], $actual['longitud'], $pendientes[$i]['latitud'], $pendientes[$i]['longitud']);
                if ($dist < $distanciaMin) {
                    $distanciaMin = $dist;
                    $mejorIndice = $i;
                }
            }

            $actual = $pendientes[$mejorIndice];
            $ordenados[] = $actual;
            array_splice($pendientes, $mejorIndice, 1);
        }

        // 3. Asignar bloques de 8
        $resultado = [];
        foreach ($ordenados as $idx => $p) {
            $ordenGlobal = $idx + 1;
            $p['orden_global'] = $ordenGlobal;
            $p['bloque_ruta'] = ceil($ordenGlobal / 8);
            $resultado[] = $p;
        }

        return $resultado;
    }

    /**
     * Calcula la distancia Haversine entre dos puntos (km).
     */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
