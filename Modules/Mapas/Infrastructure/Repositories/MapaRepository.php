<?php

namespace Modules\Mapas\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Modules\Mapas\Infrastructure\Services\RutaOptimizationService;

/**
 * Arquitecto de Software: MapaRepository (Dominio Mapas)
 *
 * Responsabilidad: Orquestar la recuperación de datos desde la BD y
 * delegar el procesamiento algorítmico al RutaOptimizationService.
 */
class MapaRepository implements MapaRepositoryInterface
{
    protected $optimizationService;

    public function __construct(RutaOptimizationService $optimizationService)
    {
        $this->optimizationService = $optimizationService;
    }

    /**
     * Devuelve solo marcadores (id, lat, lng, nombre) filtrados.
     */
    public function obtenerPuntosPacientes(array $filtros)
    {
        $query = DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'nombre_completo', 'estado', 'id_comuna');

        if (! empty($filtros['id_comuna'])) {
            $query->where('pacientes.id_comuna', $filtros['id_comuna']);
        }

        if (! empty($filtros['id_zona'])) {
            $query->join('comunas', 'pacientes.id_comuna', '=', 'comunas.id_comuna')
                ->where('comunas.id_zona', $filtros['id_zona']);
        }

        if (! empty($filtros['id_aseguradora'])) {
            $query->where('id_aseguradora', $filtros['id_aseguradora']);
        }

        if (! empty($filtros['estado'])) {
            $query->where('pacientes.estado', $filtros['estado']);
        }

        $perPage = $filtros['per_page'] ?? 500;

        return $query->orderBy('pacientes.created_at', 'DESC')->paginate((int) $perPage);
    }

    /**
     * Carga el detalle completo de un marcador específico.
     */
    public function obtenerDetallePaciente(int $id_paciente)
    {
        $paciente = DB::table('pacientes')
            ->leftJoin('aseguradoras', 'pacientes.id_aseguradora', '=', 'aseguradoras.id_aseguradora')
            ->leftJoin('barrios', 'pacientes.id_barrio', '=', 'barrios.id')
            ->select('pacientes.*', 'aseguradoras.nombre as nombre_aseguradora', 'barrios.nombre as nombre_barrio')
            ->where('id_paciente', $id_paciente)
            ->first();

        if (! $paciente) {
            return null;
        }

        $ultimaVisita = DB::table('visitas_domiciliarias')
            ->leftJoin('ordenes_servicios', 'visitas_domiciliarias.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->leftJoin('servicios', 'ordenes_servicios.id_servicio', '=', 'servicios.id_servicio')
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

        $diagnosticos = DB::table('paciente_diagnosticos')
            ->leftJoin('diagnosticos_cie10', 'paciente_diagnosticos.codigo_cie10', '=', 'diagnosticos_cie10.codigo')
            ->where('paciente_diagnosticos.id_paciente', $id_paciente)
            ->select(
                'paciente_diagnosticos.codigo_cie10 as codigo',
                'diagnosticos_cie10.descripcion',
                'paciente_diagnosticos.es_principal',
                'paciente_diagnosticos.tipo_diagnostico',
                'paciente_diagnosticos.fecha_registro',
                'paciente_diagnosticos.observacion'
            )
            ->get();

        return [
            'paciente' => $paciente,
            'ultima_visita' => $ultimaVisita,
            'diagnosticos' => $diagnosticos,
        ];
    }

    /**
     * Obtiene la ruta de visitas y delega el orden secuencial al Service.
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

        if (! empty($filtros['id_profesional'])) {
            $query->where('visitas_domiciliarias.id_personal', $filtros['id_profesional']);
        }

        if (! empty($filtros['fecha_inicio']) && ! empty($filtros['fecha_fin'])) {
            $query->whereDate('visitas_domiciliarias.fecha_realizada', '>=', $filtros['fecha_inicio'])
                ->whereDate('visitas_domiciliarias.fecha_realizada', '<=', $filtros['fecha_fin']);
        } elseif (! empty($filtros['fecha_inicio'])) {
            $query->whereDate('visitas_domiciliarias.fecha_realizada', '=', $filtros['fecha_inicio']);
        }

        $query->orderBy(DB::raw('DATE(visitas_domiciliarias.fecha_realizada)'), 'ASC')
            ->orderBy(DB::raw('TIME(visitas_domiciliarias.fecha_realizada) = "00:00:00"'), 'DESC')
            ->orderBy(DB::raw('TIME(visitas_domiciliarias.fecha_realizada)'), 'DESC');

        // Obtenemos los datos planos
        $datos = $query->get()->toArray();

        // Delegamos la lógica de contador diario al Service
        $procesados = $this->optimizationService->asignarOrdenVisitaDiario($datos);

        // Paginación manual para cumplir con el contrato esperado por el Controller
        $perPage = (int) ($filtros['per_page'] ?? 200);
        $currentPage = (int) ($filtros['page'] ?? Paginator::resolveCurrentPage() ?: 1);
        $offset = ($currentPage - 1) * $perPage;

        return new LengthAwarePaginator(
            array_slice($procesados, $offset, $perPage),
            count($procesados),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $filtros]
        );
    }

    /**
     * Predice y optimiza las rutas del mes basándose en cercanía geográfica.
     */
    public function optimizarRutasMes(array $filtros)
    {
        $mes = $filtros['mes'] ?? date('m');
        $anio = $filtros['anio'] ?? date('Y');

        $query = DB::table('visitas_domiciliarias as v')
            ->join('pacientes as p', 'v.id_paciente', '=', 'p.id_paciente')
            ->join('ordenes_servicios as os', 'v.id_orden_servicio', '=', 'os.id_orden_servicio')
            ->join('ordenes_medicas as om', 'os.id_orden', '=', 'om.id_orden')
            ->leftJoin('personal as per', 'v.id_personal', '=', 'per.id_personal')
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
                'os.frecuencia_dias',
                DB::raw('MIN(v.fecha_programada) as fecha_programada')
            )
            ->where('v.estado', 'PROGRAMADA')
            ->whereMonth('v.fecha_programada', $mes)
            ->whereYear('v.fecha_programada', $anio)
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud');

        if (! empty($filtros['id_personal'])) {
            $query->where('v.id_personal', $filtros['id_personal']);
        }

        if (! empty($filtros['id_servicio'])) {
            $query->where('os.id_servicio', $filtros['id_servicio']);
        }

        $pendientes = $query->groupBy(
            'p.id_paciente', 'p.nombre_completo', 'p.latitud', 'p.longitud',
            'p.direccion', 'p.id_comuna', 'p.id_barrio', 'p.telefono',
            'per.id_personal', 'per.nombre_completo', 'om.id_orden', 'os.frecuencia_dias'
        )->get()->toArray();

        // Delegamos algoritmo de vecino cercano y agrupamiento al Service
        $ordenados = $this->optimizationService->optimizarPorVecinoCercano($pendientes);

        return $this->optimizationService->agruparEnBloques($ordenados, 8);
    }

    /**
     * Obtiene los datos base para la optimización (Pacientes + Ordenes + Última Visita).
     * No aplica algoritmos, solo extrae la información cruda.
     */
    public function obtenerDatosBaseOptimizacion(array $filtros)
    {
        $mes = $filtros['mes'] ?? date('m');
        $anio = $filtros['anio'] ?? date('Y');

        $query = DB::table('visitas_domiciliarias as v')
            ->join('pacientes as p', 'v.id_paciente', '=', 'p.id_paciente')
            ->join('ordenes_servicios as os', 'v.id_orden_servicio', '=', 'os.id_orden_servicio')
            ->join('ordenes_medicas as om', 'os.id_orden', '=', 'om.id_orden')
            ->leftJoin('personal as per', 'v.id_personal', '=', 'per.id_personal')
            ->select(
                'p.id_paciente',
                'p.nombre_completo as paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.telefono',
                'os.frecuencia_dias',
                'om.fecha_orden',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                DB::raw('MIN(v.fecha_programada) as fecha_programada')
            )
            ->where('v.estado', 'PROGRAMADA')
            ->whereMonth('v.fecha_programada', $mes)
            ->whereYear('v.fecha_programada', $anio)
            ->whereNotNull('p.latitud')
            ->where('p.latitud', '!=', 0);

        $idProfesional = $filtros['id_personal'] ?? $filtros['id_profesional'] ?? null;
        if (! empty($idProfesional)) {
            $query->where('v.id_personal', $idProfesional);
        }

        if (! empty($filtros['id_servicio'])) {
            $query->where('os.id_servicio', $filtros['id_servicio']);
        }

        return $query->groupBy(
            'p.id_paciente', 'p.nombre_completo', 'p.latitud', 'p.longitud',
            'p.direccion', 'p.telefono', 'os.frecuencia_dias', 'om.fecha_orden', 'per.id_personal', 'per.nombre_completo'
        )->get()->toArray();
    }

    /**
     * Obtiene los datos base para la predicción de visitas (Pacientes + Ordenes Servicios + Última Visita).
     * No aplica algoritmos, solo extrae la información cruda.
     */
    public function obtenerDatosBasePrediccion(array $filtros)
    {
        $idProfesional = $filtros['id_personal'] ?? $filtros['id_profesional'] ?? null;

        $latestOrderService = DB::table('ordenes_servicios as os_sub')
            ->join('ordenes_medicas as om_sub', 'os_sub.id_orden', '=', 'om_sub.id_orden')
            ->join('ingresos as i_sub', 'om_sub.id_ingreso', '=', 'i_sub.id_ingreso')
            ->select('i_sub.id_paciente', 'os_sub.id_servicio', DB::raw('MAX(os_sub.id_orden_servicio) as max_id_orden_servicio'))
            ->groupBy('i_sub.id_paciente', 'os_sub.id_servicio');

        $ultimaVisitaGlobal = DB::table('visitas_domiciliarias as vd')
            ->join('ordenes_servicios as os_sub2', 'vd.id_orden_servicio', '=', 'os_sub2.id_orden_servicio')
            ->select('vd.id_paciente', 'os_sub2.id_servicio', DB::raw('MAX(vd.fecha_realizada) as ultima_visita'))
            ->where('vd.estado', 'COMPLETADA')
            ->groupBy('vd.id_paciente', 'os_sub2.id_servicio');

        $sesionesActuales = DB::table('visitas_domiciliarias')
            ->select('id_orden_servicio', DB::raw('COUNT(*) as sesiones_completadas'))
            ->where('estado', 'COMPLETADA')
            ->groupBy('id_orden_servicio');

        $query = DB::table('ordenes_servicios as os')
            ->join('ordenes_medicas as om', 'os.id_orden', '=', 'om.id_orden')
            ->join('ingresos as i', 'om.id_ingreso', '=', 'i.id_ingreso')
            ->join('pacientes as p', 'i.id_paciente', '=', 'p.id_paciente')
            ->joinSub($latestOrderService, 'los', 'os.id_orden_servicio', '=', 'los.max_id_orden_servicio')
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            ->leftJoin('personal as per', 'os.id_profesional_asignado', '=', 'per.id_personal')
            ->leftJoinSub($ultimaVisitaGlobal, 'uv', function ($join) {
                $join->on('p.id_paciente', '=', 'uv.id_paciente')
                     ->on('os.id_servicio', '=', 'uv.id_servicio');
            })
            ->leftJoinSub($sesionesActuales, 'sa', function ($join) {
                $join->on('os.id_orden_servicio', '=', 'sa.id_orden_servicio');
            })
            ->select(
                'p.id_paciente',
                'p.nombre_completo as paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.telefono',
                'os.frecuencia_dias',
                'os.numero_sesiones',
                'os.fecha_inicio',
                'om.fecha_orden',
                's.nombre_servicio as servicio',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'uv.ultima_visita',
                DB::raw('COALESCE(sa.sesiones_completadas, 0) as sesiones_completadas')
            )
            ->whereNotNull('p.latitud')
            ->where('p.latitud', '!=', 0);

        if (! empty($idProfesional)) {
            $query->where('os.id_profesional_asignado', $idProfesional);
        }

        if (! empty($filtros['id_servicio'])) {
            $query->where('os.id_servicio', $filtros['id_servicio']);
        }

        return $query->get()->toArray();
    }

    /**
     * Otros métodos CRUD básicos.
     */
    public function obtenerOrdenesPaciente(int $id_paciente)
    {
        $paciente = DB::table('pacientes')
            ->select('id_paciente', 'identificacion', 'nombre_completo', 'telefono', 'direccion')
            ->where('id_paciente', $id_paciente)
            ->first();

        if (! $paciente) {
            return null;
        }

        $ordenes = DB::table('ordenes_medicas as om')
            ->join('ingresos as i', 'om.id_ingreso', '=', 'i.id_ingreso')
            ->join('ordenes_servicios as os', 'om.id_orden', '=', 'os.id_orden')
            ->leftJoin('personal as per', 'os.id_profesional_asignado', '=', 'per.id_personal')
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            ->where('i.id_paciente', $id_paciente)
            ->select(
                'om.id_orden',
                'om.fecha_orden',
                'os.numero_sesiones',
                'os.frecuencia_dias',
                'om.estado as estado_orden',
                'per.id_personal as id_profesional',
                'per.nombre_completo as nombre_profesional',
                's.nombre_servicio as servicio'
            )
            ->orderBy('om.fecha_orden', 'DESC')
            ->get();

        return ['paciente' => $paciente, 'ordenes' => $ordenes];
    }

    public function obtenerPacientesPorComuna(int $id_comuna)
    {
        return DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'url_google_maps', 'identificacion', 'nombre_completo')
            ->where('id_comuna', $id_comuna)
            ->get();
    }

    public function obtenerTodosLosPuntos(array $filtros)
    {
        $query = DB::table('pacientes')
            ->select('id_paciente', 'latitud', 'longitud', 'nombre_completo', 'estado', 'id_comuna');

        if (! empty($filtros['id_comuna'])) {
            $query->where('pacientes.id_comuna', $filtros['id_comuna']);
        }
        if (! empty($filtros['id_zona'])) {
            $query->join('comunas', 'pacientes.id_comuna', '=', 'comunas.id_comuna')
                ->where('comunas.id_zona', $filtros['id_zona']);
        }
        if (! empty($filtros['id_aseguradora'])) {
            $query->where('id_aseguradora', $filtros['id_aseguradora']);
        }
        if (! empty($filtros['estado'])) {
            $query->where('pacientes.estado', $filtros['estado']);
        }

        return $query->orderBy('pacientes.created_at', 'DESC')->get();
    }

    public function optimizarRutasMesMetodoOrden(array $filtros)
    {
        return [];
    }

    public function optimizarRutasMesCercania(array $filtros)
    {
        return [];
    }

    public function obtenerVisitasProgramadas(array $filtros)
    {
        $mes = $filtros['mes'] ?? date('m');
        $anio = $filtros['anio'] ?? date('Y');

        $query = DB::table('visitas_domiciliarias as v')
            ->join('pacientes as p', 'v.id_paciente', '=', 'p.id_paciente')
            ->join('ordenes_servicios as os', 'v.id_orden_servicio', '=', 'os.id_orden_servicio')
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            ->leftJoin('personal as per', 'v.id_personal', '=', 'per.id_personal')
            ->select(
                'v.id_visita',
                'p.id_paciente',
                'p.nombre_completo as paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.telefono',
                'os.id_orden_servicio',
                'os.frecuencia_dias',
                's.id_servicio',
                's.nombre_servicio',
                's.codigo_servicio',
                'per.id_personal',
                'per.nombre_completo as nombre_profesional',
                'v.fecha_programada',
                'v.estado'
            )
            ->where('v.estado', 'PROGRAMADA')
            ->whereMonth('v.fecha_programada', $mes)
            ->whereYear('v.fecha_programada', $anio)
            ->whereNotNull('p.latitud')
            ->where('p.latitud', '!=', 0);

        $idProfesional = $filtros['id_personal'] ?? $filtros['id_profesional'] ?? null;
        if (! empty($idProfesional)) {
            $query->where('v.id_personal', $idProfesional);
        }

        if (! empty($filtros['id_servicio'])) {
            $query->where('os.id_servicio', $filtros['id_servicio']);
        }

        return $query->get()->toArray();
    }
}
