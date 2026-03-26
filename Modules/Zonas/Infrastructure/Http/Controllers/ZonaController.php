<?php

namespace Modules\Zonas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Zonas\Application\UseCases\CrearZona;
use Modules\Zonas\Application\UseCases\ActualizarZona;
use Modules\Zonas\Application\UseCases\EliminarZona;
use Modules\Zonas\Application\UseCases\ListarZonas;
use OpenApi\Attributes as OA;

class ZonaController
{
    #[OA\Get(
        path: '/api/v1/zonas',
        summary: 'Listar todas las zonas',
        security: [['bearerAuth' => []]],
        tags: ['Zonas']
    )]
    #[OA\Response(response: 200, description: 'Listado de zonas')]
    public function index(ListarZonas $useCase)
    {
        try {
            $zonas = $useCase->execute();
            return response()->json(['data' => $zonas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/zonas',
        summary: 'Crear nueva zona',
        security: [['bearerAuth' => []]],
        tags: ['Zonas']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Norte')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Zona creada exitosamente')]
    public function store(Request $request, CrearZona $useCase)
    {
        try {
            $zona = $useCase->execute($request->all());
            return response()->json(['message' => 'Zona creada exitosamente', 'data' => $zona], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/zonas/{id}',
        summary: 'Actualizar una zona',
        security: [['bearerAuth' => []]],
        tags: ['Zonas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la zona', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Sur')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Zona actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarZona $useCase)
    {
        try {
            $zona = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Zona actualizada exitosamente', 'data' => $zona], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/zonas/{id}',
        summary: 'Eliminar una zona',
        security: [['bearerAuth' => []]],
        tags: ['Zonas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la zona', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Zona eliminada exitosamente')]
    public function destroy($id, EliminarZona $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Zona eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
