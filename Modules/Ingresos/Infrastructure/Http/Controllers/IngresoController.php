<?php

namespace Modules\Ingresos\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ingresos\Application\UseCases\CrearIngreso;
use Modules\Ingresos\Application\UseCases\ListarIngresos;
use Modules\Ingresos\Application\UseCases\ObtenerAutorizacionesPorPaciente;
use Modules\Ingresos\Application\UseCases\DetectarAutorizacionEnUso;
use Modules\Ingresos\Application\UseCases\CrearIngresoConSesiones;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Ingresos', description: 'Endpoints para la gestión de ingresos de pacientes')]
class IngresoController extends Controller
{
    #[OA\Get(
        path: '/api/v1/ingresos',
        summary: 'Listar todos los ingresos',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos obtenida correctamente'
    )]
    public function index(ListarIngresos $useCase)
    {
        try {
            $ingresos = $useCase->execute();
            return response()->json(['data' => $ingresos], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ingresos',
        summary: 'Registrar un nuevo ingreso',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['ingreso', 'id_paciente', 'fecha_ingreso'],
                properties: [
                    new OA\Property(property: 'ingreso', type: 'integer', example: 12345),
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'autorizacion', type: 'string', example: 'AUT-001'),
                    new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date-time', example: '2024-04-28 10:00:00')
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Ingreso registrado correctamente'
    )]
    #[OA\Response(
        response: 400,
        description: 'Error en la solicitud'
    )]
    public function store(Request $request, CrearIngreso $useCase)
    {
        try {
            $nuevoIngreso = $useCase->execute($request->all());
            return response()->json(['data' => $nuevoIngreso], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/ingresos/paciente/{idPaciente}/autorizaciones',
        summary: 'Obtener todas las autorizaciones de un paciente',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos'],
        parameters: [
            new OA\Parameter(
                name: 'idPaciente',
                in: 'path',
                required: true,
                description: 'ID del paciente',
                schema: new OA\Schema(type: 'integer')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de autorizaciones obtenida correctamente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'autorizacion', type: 'string', example: 'AUT-2024-001'),
                                new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date-time', example: '2024-04-28 10:00:00'),
                                new OA\Property(property: 'ingreso', type: 'integer', example: 12345),
                                new OA\Property(property: 'estado', type: 'string', example: 'VIGENTE')
                            ]
                        )
                    )
                ]
            )
        )
    )]
    public function autorizacionesPorPaciente($idPaciente, ObtenerAutorizacionesPorPaciente $useCase)
    {
        try {
            $autorizaciones = $useCase->execute($idPaciente);
            return response()->json(['data' => $autorizaciones], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/ingresos/buscar',
        summary: 'Buscar ingresos exclusivamente por número de ingreso',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos'],
        parameters: [
            new OA\Parameter(
                name: 'q', 
                in: 'query', 
                required: false, 
                description: 'Término de búsqueda', 
                schema: new OA\Schema(type: 'string')
            )
        ]
    )]
    #[OA\Response(
        response: 200, 
        description: 'Resultados de búsqueda',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_ingreso', type: 'integer', example: 1),
                                new OA\Property(property: 'ingreso', type: 'integer', example: 12345),
                                new OA\Property(property: 'id_paciente', type: 'integer', example: 10),
                                new OA\Property(property: 'autorizacion', type: 'string', example: 'AUT-001'),
                                new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date-time', example: '2024-04-28 10:00:00'),
                                new OA\Property(property: 'identificacion', type: 'string', example: '1090123456'),
                                new OA\Property(property: 'nombre_completo', type: 'string', example: 'Juan Perez')
                            ]
                        )
                    )
                ]
            )
        )
    )]
    public function buscar(Request $request, \Modules\Ingresos\Application\UseCases\BuscarIngreso $useCase)
    {
        try {
            $busqueda = $request->query('q', '');
            if (empty(trim($busqueda))) {
                return response()->json(['data' => []], 200);
            }
            $ingresos = $useCase->execute($busqueda);
            return response()->json(['data' => $ingresos], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/ingresos/verificar-autorizacion',
        summary: 'Detectar si una autorización ya está en uso',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos'],
        parameters: [
            new OA\Parameter(
                name: 'autorizacion',
                in: 'query',
                required: true,
                description: 'Código de autorización a validar',
                schema: new OA\Schema(type: 'string')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Resultado de la verificación si la autorización está en uso',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'en_uso', type: 'boolean', example: true),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'La autorización ya está en uso')
                ]
            )
        )
    )]
    public function verificarAutorizacion(Request $request, DetectarAutorizacionEnUso $useCase)
    {
        try {
            $autorizacion = $request->query('autorizacion', '');
            $resultado = $useCase->execute($autorizacion);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ingresos/con-sesiones',
        summary: 'Crear un ingreso completo con su orden médica, servicios y primera visita',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'autorizacion', 'servicios'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 16),
                    new OA\Property(property: 'autorizacion', type: 'string', example: '4292224'),
                    new OA\Property(property: 'observacion', type: 'string', example: 'observación de pruebas'),
                    new OA\Property(
                        property: 'servicios',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_servicio', type: 'integer', example: 1),
                                new OA\Property(property: 'id_profesional', type: 'integer', example: 1),
                                new OA\Property(property: 'numero_sesiones', type: 'integer', example: 1),
                                new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 1),
                                new OA\Property(property: 'fecha_inicio', type: 'string', example: '2026-05-21 13:35'),
                                new OA\Property(property: 'fecha_programada', type: 'string', example: '2026-05-21 13:35')
                            ]
                        )
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Ingreso y sesiones creados con éxito'
    )]
    #[OA\Response(
        response: 400,
        description: 'Error en la solicitud o validación'
    )]
    public function crearConSesiones(Request $request, CrearIngresoConSesiones $useCase)
    {
        try {
            $usuarioId = (int) auth()->user()->id_usuario;
            $resultado = $useCase->execute($request->all(), $usuarioId);
            return response()->json(['data' => $resultado], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
