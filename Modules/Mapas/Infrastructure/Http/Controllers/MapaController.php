<?php

namespace Modules\Mapas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Mapas\Application\UseCases\ObtenerPuntosMapa;
use Modules\Mapas\Application\UseCases\ObtenerTodosLosPuntosMapa;
use Modules\Mapas\Application\UseCases\ObtenerDetallePunto;
use Modules\Mapas\Application\UseCases\ObtenerPacientesPorComuna;
use Modules\Mapas\Application\UseCases\OptimizarRutasMensuales;
use Modules\Mapas\Application\UseCases\OptimizarRutasMesMetodoOrden;
use Modules\Mapas\Application\UseCases\OptimizarRutasMesCercania;
use Modules\Mapas\Application\UseCases\OptimizarRutaPaciente;
use OpenApi\Attributes as OA;

class MapaController
{
    #[OA\Get(
        path: '/api/v1/mapas/pacientes/puntos',
        summary: 'Obtener marcadores livianos para el mapa (80k pacientes optimizado)',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'id_zona', description: 'Filtrar por Zona', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'id_comuna', description: 'Filtrar por Comuna', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'id_aseguradora', description: 'Filtrar por EPS', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'estado', description: 'Filtrar por estado del paciente', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'per_page', description: 'Cantidad por página (por defecto 500)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'page', description: 'Número de página', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Lista de puntos coordinados para el mapa con metadatos de paginación')]
    public function getMarkers(Request $request, ObtenerPuntosMapa $useCase)
    {
        try {
            $filtros = $request->only(['id_zona', 'id_comuna', 'id_aseguradora', 'estado', 'per_page', 'page']);
            $paginador = $useCase->execute($filtros);
            
            // Retornamos una versión "Lite" optimizada
            return response()->json([
                'total'        => $paginador->total(),
                'per_page'     => $paginador->perPage(),
                'current_page' => $paginador->currentPage(),
                'last_page'    => $paginador->lastPage(),
                'data'         => $paginador->items(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/pacientes/detalle/{id}',
        summary: 'Obtener el detalle clínico y operativo de un punto seleccionado',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Información detallada para ser cargada en un popup/modal')]
    public function getDetail(int $id, ObtenerDetallePunto $useCase)
    {
        try {
            $detalle = $useCase->execute($id);
            return response()->json(['data' => $detalle], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/rutas-visitas',
        summary: 'Obtener la ruta de visitas de pacientes organizadas por fecha y profesional',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'fecha_inicio', description: 'Fecha de inicio (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'fecha_fin', description: 'Fecha de fin (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'id_profesional', description: 'Filtrar por ID del profesional', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'per_page', description: 'Cantidad por página (por defecto 200)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'page', description: 'Número de página', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Rutas de visitas numeradas')]
    public function getRutaVisitas(Request $request, \Modules\Mapas\Application\UseCases\ObtenerRutaVisitas $useCase)
    {
        try {
            $filtros = [
                'fecha_inicio'   => $request->query('fecha_inicio'),
                'fecha_fin'      => $request->query('fecha_fin'),
                'id_profesional' => $request->query('id_profesional'),
                'per_page'       => $request->query('per_page'),
                'page'           => $request->query('page')
            ];

            $paginador = $useCase->execute($filtros);

            return response()->json([
                'success'      => true,
                'total'        => $paginador->total(),
                'per_page'     => $paginador->perPage(),
                'current_page' => $paginador->currentPage(),
                'last_page'    => $paginador->lastPage(),
                'data'         => $paginador->items()
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/pacientes/{id}/ordenes',
        summary: 'Obtener las órdenes médicas de un paciente junto con su profesional asociado',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Datos del paciente con sus ordenes médicas')]
    public function getOrdenesPaciente(int $id, \Modules\Mapas\Application\UseCases\ObtenerOrdenesPaciente $useCase)
    {
        try {
            $resultado = $useCase->execute($id);

            return response()->json([
                'success' => true,
                'data'    => $resultado
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 500;
            $status = $status >= 400 && $status < 600 ? $status : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/comunas/{id}/pacientes',
        summary: 'Obtener todos los pacientes de una comuna específica con datos geográficos',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la comuna', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200, 
        description: 'Lista de pacientes de la comuna con geolocalización básica',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data', 
                    type: 'array', 
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                            new OA\Property(property: 'latitud', type: 'string', example: '7.12345678'),
                            new OA\Property(property: 'longitud', type: 'string', example: '-73.12345678'),
                            new OA\Property(property: 'url_google_maps', type: 'string', example: 'https://maps.google.com/?q=...'),
                            new OA\Property(property: 'identificacion', type: 'string', example: '12345678'),
                            new OA\Property(property: 'nombre_completo', type: 'string', example: 'JUAN PEREZ')
                        ]
                    )
                )
            ]
        )
    )]
    public function getPacientesPorComuna(int $id, ObtenerPacientesPorComuna $useCase)
    {
        try {
            $pacientes = $useCase->execute($id);
            return response()->json([
                'success' => true,
                'data'    => $pacientes
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/pacientes/puntos/todos',
        summary: 'Obtener todos los marcadores livianos para el mapa (Sin paginación)',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'id_comuna', description: 'Filtrar por Comuna', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'id_aseguradora', description: 'Filtrar por EPS', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'estado', description: 'Filtrar por estado del paciente', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Lista completa de puntos coordinados para el mapa')]
    public function getAllMarkers(Request $request, ObtenerTodosLosPuntosMapa $useCase)
    {
        try {
            $filtros = $request->only(['id_comuna', 'id_aseguradora', 'estado']);
            $puntos = $useCase->execute($filtros);
            
            return response()->json([
                'success' => true,
                'total'   => count($puntos),
                'data'    => $puntos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/rutas-optimizadas',
        summary: 'Obtener mega listado de todos los pacientes, optimizado por cercanía en bloques de 8 (Sin parámetros)',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Response(response: 200, description: 'Mega listado de todos los pacientes iterados por cercanía de principio a fin')]
    public function getRutasOptimizadas(Request $request, OptimizarRutasMensuales $useCase)
    {
        try {
            $resultado = $useCase->execute([]);

            return response()->json([
                'success' => true,
                'total'   => count($resultado),
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    #[OA\Get(
        path: '/api/v1/mapas/rutas-optimizadas-orden',
        summary: 'Optimizar rutas mensuales basándose en el campo orden_mapa de los pacientes',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'mes', description: 'Mes a proyectar (1-12)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'anio', description: 'Año a proyectar', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Rutas proyectadas ordenadas por orden_mapa')]
    public function getRutasOptimizadasPorOrden(Request $request, OptimizarRutasMesMetodoOrden $useCase)
    {
        try {
            $filtros = $request->only(['mes', 'anio']);
            $resultado = $useCase->execute($filtros);

            return response()->json([
                'success' => true,
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/rutas-optimizadas-cercania',
        summary: 'Optimizar rutas mensuales basándose en cercanía geográfica (Mínimo 8 pacientes)',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'mes', description: 'Mes a proyectar (1-12)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'anio', description: 'Año a proyectar', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'id_personal', description: 'ID del profesional médico (Requerido)', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200, 
        description: 'Mega listado de rutas organizadas por cercanía',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'total', type: 'integer', example: 24),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id_paciente', type: 'integer'),
                            new OA\Property(property: 'nombre_paciente', type: 'string'),
                            new OA\Property(property: 'direccion', type: 'string'),
                            new OA\Property(property: 'latitud', type: 'string'),
                            new OA\Property(property: 'longitud', type: 'string'),
                            new OA\Property(property: 'numero_ruta', type: 'integer', description: 'Identificador del bloque/día de ruta'),
                            new OA\Property(property: 'orden_en_ruta', type: 'integer', description: 'Posición secuencial dentro de esa ruta')
                        ]
                    )
                )
            ]
        )
    )]
    public function getRutasOptimizadasPorCercania(Request $request, OptimizarRutasMesCercania $useCase)
    {
        try {
            $filtros = $request->only(['mes', 'anio', 'id_personal']);
            $resultado = $useCase->execute($filtros);

            return response()->json([
                'success' => true,
                'total'   => count($resultado),
                'data'    => $resultado
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/mapas/rutas-optimizadas-global',
        summary: 'Optimizar rutas globales por cercanía (Script Python)',
        security: [['bearerAuth' => []]],
        tags: ['Mapas']
    )]
    #[OA\Parameter(name: 'mes', description: 'Mes a proyectar (1-12)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'anio', description: 'Año a proyectar', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200, 
        description: 'Rutas proyectadas organizadas secuencialmente por cercanía global',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'total', type: 'integer', example: 150),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id_paciente', type: 'integer'),
                            new OA\Property(property: 'nombre_paciente', type: 'string'),
                            new OA\Property(property: 'direccion', type: 'string'),
                            new OA\Property(property: 'telefono', type: 'string'),
                            new OA\Property(property: 'latitud', type: 'number', format: 'float'),
                            new OA\Property(property: 'longitud', type: 'number', format: 'float'),
                            new OA\Property(property: 'fecha_proyectada', type: 'string', format: 'date'),
                            new OA\Property(property: 'orden_global', type: 'integer', description: 'Secuencia lógica uno a uno'),
                            new OA\Property(property: 'bloque_ruta', type: 'integer', description: 'Grupo de 8 pacientes para planeación diaria')
                        ]
                    )
                )
            ]
        )
    )]
    public function getRutasGlobales(Request $request, OptimizarRutaPaciente $useCase)
    {
        try {
            $filtros = $request->only(['mes', 'anio']);
            $resultado = $useCase->execute($filtros);

            return response()->json([
                'success' => true,
                'total'   => count($resultado),
                'data'    => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
