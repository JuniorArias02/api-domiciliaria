<?php

namespace Modules\Mapas\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
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
            ->leftJoin('barrios', 'pacientes.id_barrio', '=', 'barrios.id_barrio')
            ->select('pacientes.*', 'aseguradoras.nombre as nombre_aseguradora', 'barrios.nombre as nombre_barrio')
            ->where('id_paciente', $id_paciente)
            ->first();

        if (! $paciente) {
            return null;
        }

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

        $diagnosticos = DB::table('paciente_diagnosticos')
            ->leftJoin('diagnosticos_cie10', 'paciente_diagnosticos.codigo_cie10', '=', 'diagnosticos_cie10.codigo')
            ->where('id_paciente', $id_paciente)
            ->select('diagnosticos_cie10.codigo', 'diagnosticos_cie10.descripcion', 'paciente_diagnosticos.es_principal')
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
        return $this->optimizationService->asignarOrdenVisitaDiario($datos);
    }

    /**
     * Predice y optimiza las rutas del mes basándose en cercanía geográfica.
     */
    public function optimizarRutasMes(array $filtros)
    {
        $ultimaVisitaId = DB::table('visitas_domiciliarias')
            ->select('id_paciente', DB::raw('MAX(id_visita) as max_id'))
            ->where('estado', 'COMPLETADA')
            ->groupBy('id_paciente');

        $ultimaVisita = DB::table('visitas_domiciliarias as vd')
            ->joinSub($ultimaVisitaId, 'uv_id', function ($join) {
                $join->on('vd.id_visita', '=', 'uv_id.max_id');
            })
            ->select('vd.id_paciente', 'vd.fecha_realizada', 'vd.id_personal');

        $query = DB::table('pacientes as p')
            ->join('ingresos as i', 'p.id_paciente', '=', 'i.id_paciente')
            ->join('ordenes_medicas as om', 'i.id_ingreso', '=', 'om.id_ingreso')
            ->join('ordenes_servicios as os', 'om.id_orden', '=', 'os.id_orden')
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
                'om.fecha_orden',
                'os.frecuencia_dias',
                'uv.fecha_realizada as ultima_visita'
            )
            ->where('om.estado', 'VIGENTE')
            ->whereNotNull('p.latitud')
            ->whereNotNull('p.longitud');

        if (! empty($filtros['id_personal'])) {
            $query->where('per.id_personal', $filtros['id_personal']);
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

        // 1. Obtener el ID de la última visita de cada paciente
        $ultimaVisitaId = DB::table('visitas_domiciliarias')
            ->select('id_paciente', DB::raw('MAX(id_visita) as max_id'))
            ->groupBy('id_paciente');

        // 2. Traer los datos de esa última visita específica
        $ultimaVisita = DB::table('visitas_domiciliarias as vd')
            ->joinSub($ultimaVisitaId, 'uv_id', function ($join) {
                $join->on('vd.id_visita', '=', 'uv_id.max_id');
            })
            ->select('vd.id_paciente', 'vd.fecha_realizada', 'vd.id_personal');

        // 3. Query final uniendo con pacientes y sus órdenes vigentes
        $query = DB::table('pacientes as p')
            ->join('ingresos as i', 'p.id_paciente', '=', 'i.id_paciente')
            ->join('ordenes_medicas as om', 'i.id_ingreso', '=', 'om.id_ingreso')
            ->join('ordenes_servicios as os', 'om.id_orden', '=', 'os.id_orden')
            ->leftJoinSub($ultimaVisita, 'uv', function ($join) {
                $join->on('p.id_paciente', '=', 'uv.id_paciente');
            })
            ->select(
                'p.id_paciente',
                'p.nombre_completo as paciente',
                'p.latitud',
                'p.longitud',
                'p.direccion',
                'p.telefono',
                'os.frecuencia_dias',
                'om.fecha_orden',
                'uv.id_personal',
                'uv.fecha_realizada as ultima_visita'
            )
            ->where('om.estado', 'VIGENTE')
            ->whereNotNull('p.latitud')
            ->where('p.latitud', '!=', 0);

        if (! empty($filtros['id_personal'])) {
            $query->where('uv.id_personal', $filtros['id_personal']);
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
                's.nombre_servicio as especialidad'
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
}
