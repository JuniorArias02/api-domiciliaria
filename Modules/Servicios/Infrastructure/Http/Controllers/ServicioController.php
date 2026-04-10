<?php

namespace Modules\Servicios\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Servicios\Application\UseCases\CrearServicio;
use Modules\Servicios\Application\UseCases\ActualizarServicio;
use Modules\Servicios\Application\UseCases\EliminarServicio;
use Modules\Servicios\Application\UseCases\ListarServicios;
use Modules\Servicios\Application\UseCases\ObtenerServicio;
use OpenApi\Attributes as OA;

class ServicioController
{
    #[OA\Get(
        path: '/api/v1/servicios',
        summary: 'Listar todos los servicios',
        security: [['bearerAuth' => []]],
        tags: ['Servicios']
    )]
    #[OA\Response(response: 200, description: 'Listado de servicios')]
    public function index(ListarServicios $useCase)
    {
        try {
            $servicios = $useCase->execute();
            return response()->json(['data' => $servicios], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/servicios/{id}',
        summary: 'Obtener un servicio por ID',
        security: [['bearerAuth' => []]],
        tags: ['Servicios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del servicio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Detalle del servicio')]
    public function show($id, ObtenerServicio $useCase)
    {
        try {
            $servicio = $useCase->execute((int)$id);
            return response()->json(['data' => $servicio], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    #[OA\Post(
        path: '/api/v1/servicios',
        summary: 'Crear un nuevo servicio',
        security: [['bearerAuth' => []]],
        tags: ['Servicios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['codigo_servicio', 'nombre_servicio'],
                properties: [
                    new OA\Property(property: 'codigo_servicio', type: 'string', example: 'S001'),
                    new OA\Property(property: 'nombre_servicio', type: 'string', example: 'Consulta Médica General'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Atención primaria en salud')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Servicio creado exitosamente')]
    public function store(Request $request, CrearServicio $useCase)
    {
        try {
            $servicio = $useCase->execute($request->all());
            return response()->json(['message' => 'Servicio creado exitosamente', 'data' => $servicio], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Put(
        path: '/api/v1/servicios/{id}',
        summary: 'Actualizar un servicio',
        security: [['bearerAuth' => []]],
        tags: ['Servicios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del servicio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'nombre_servicio', type: 'string', example: 'Consulta Médica Especializada'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Atención por especialista')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Servicio actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarServicio $useCase)
    {
        try {
            $servicio = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Servicio actualizado exitosamente', 'data' => $servicio], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Delete(
        path: '/api/v1/servicios/{id}',
        summary: 'Eliminar un servicio',
        security: [['bearerAuth' => []]],
        tags: ['Servicios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del servicio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Servicio eliminado exitosamente')]
    public function destroy($id, EliminarServicio $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Servicio eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
