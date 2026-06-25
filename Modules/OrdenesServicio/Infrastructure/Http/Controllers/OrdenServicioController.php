<?php

namespace Modules\OrdenesServicio\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OrdenesServicio\Application\UseCases\CrearOrdenServicio;
use Modules\OrdenesServicio\Application\UseCases\ListarOrdenesServicio;
use Modules\OrdenesServicio\Application\UseCases\ObtenerServiciosPorAutorizacion;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'OrdenesServicio', description: 'Endpoints para la gestión de órdenes de servicios')]
class OrdenServicioController extends Controller
{
    #[OA\Get(
        path: '/api/v1/ordenes-servicio/pendientes-por-autorizacion',
        summary: 'Obtener servicios con sesiones pendientes para una autorización',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio'],
        parameters: [
            new OA\Parameter(
                name: 'autorizacion',
                in: 'query',
                required: true,
                description: 'Código de autorización a consultar',
                schema: new OA\Schema(type: 'string')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de servicios con sesiones pendientes obtenida correctamente'
    )]
    public function serviciosPendientesPorAutorizacion(Request $request, ObtenerServiciosPorAutorizacion $useCase)
    {
        try {
            $autorizacion = $request->query('autorizacion');
            if (empty($autorizacion)) {
                return response()->json(['error' => 'El parámetro autorizacion es requerido'], 400);
            }
            $servicios = $useCase->execute($autorizacion);
            return response()->json(['data' => $servicios], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    #[OA\Get(
        path: '/api/v1/ordenes-servicio',
        summary: 'Listar todas las órdenes de servicio',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de órdenes de servicio obtenida correctamente'
    )]
    public function index(ListarOrdenesServicio $useCase)
    {
        try {
            $ordenes = $useCase->execute();
            return response()->json(['data' => $ordenes], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ordenes-servicio',
        summary: 'Registrar una nueva orden de servicio',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_orden', 'id_servicio', 'numero_sesiones'],
                properties: [
                    new OA\Property(property: 'id_orden', type: 'integer', example: 1),
                    new OA\Property(property: 'id_servicio', type: 'integer', example: 5),
                    new OA\Property(property: 'id_profesional_asignado', type: 'integer', example: 10),
                    new OA\Property(property: 'numero_sesiones', type: 'integer', example: 10),
                    new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 2),
                    new OA\Property(property: 'estado', type: 'string', example: 'PROGRAMADA'),
                    new OA\Property(property: 'id_orden_servicio_anterior', type: 'integer', example: 500, nullable: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Orden de servicio registrada correctamente'
    )]
    public function store(Request $request, CrearOrdenServicio $useCase)
    {
        try {
            $nuevaOrden = $useCase->execute($request->all());
            return response()->json(['data' => $nuevaOrden], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/ordenes-servicio/historial/paciente/{idPaciente}',
        summary: 'Obtener el historial de tratamientos de un paciente',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio'],
        parameters: [
            new OA\Parameter(
                name: 'idPaciente',
                in: 'path',
                required: true,
                description: 'ID del paciente',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'id_servicio',
                in: 'query',
                required: false,
                description: 'ID del servicio para filtrar el historial (ej. solo Fisioterapia)',
                schema: new OA\Schema(type: 'integer')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Historial obtenido correctamente'
    )]
    public function historialPorPaciente(Request $request, $idPaciente, \Modules\OrdenesServicio\Application\UseCases\ObtenerHistorialServiciosPaciente $useCase)
    {
        try {
            $idServicio = $request->query('id_servicio') ? (int) $request->query('id_servicio') : null;
            $historial = $useCase->execute((int)$idPaciente, $idServicio);
            return response()->json(['data' => $historial], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/ordenes-servicio/continuidad/buscar',
        summary: 'Búsqueda avanzada de continuidades de tratamiento',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio'],
        parameters: [
            new OA\Parameter(
                name: 'id_paciente',
                in: 'query',
                required: true,
                description: 'ID del paciente',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'id_servicio',
                in: 'query',
                required: false,
                description: 'ID del servicio',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'numero_ingreso',
                in: 'query',
                required: false,
                description: 'Número de ingreso para filtrar',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'autorizacion',
                in: 'query',
                required: false,
                description: 'Número de autorización',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mes_inicio',
                in: 'query',
                required: false,
                description: 'Mes inicial para filtrar (YYYY-MM)',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mes_fin',
                in: 'query',
                required: false,
                description: 'Mes final para filtrar (YYYY-MM)',
                schema: new OA\Schema(type: 'string')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Resultados de la búsqueda obtenidos correctamente'
    )]
    public function buscarContinuidades(Request $request, \Modules\OrdenesServicio\Application\UseCases\BuscarContinuidadesAvanzado $useCase)
    {
        try {
            $idPaciente = $request->query('id_paciente');
            if (empty($idPaciente)) {
                return response()->json(['error' => 'El parámetro id_paciente es requerido'], 400);
            }

            $filtros = [
                'id_servicio' => $request->query('id_servicio'),
                'numero_ingreso' => $request->query('numero_ingreso'),
                'autorizacion' => $request->query('autorizacion'),
                'mes_inicio' => $request->query('mes_inicio'),
                'mes_fin' => $request->query('mes_fin')
            ];

            // Limpiar filtros nulos
            $filtros = array_filter($filtros, function($value) {
                return !is_null($value) && $value !== '';
            });

            $resultados = $useCase->execute((int)$idPaciente, $filtros);
            return response()->json(['data' => $resultados], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
