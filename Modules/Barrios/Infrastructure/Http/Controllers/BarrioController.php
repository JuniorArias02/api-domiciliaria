<?php

namespace Modules\Barrios\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Barrios\Application\UseCases\CrearBarrio;
use Modules\Barrios\Application\UseCases\ActualizarBarrio;
use Modules\Barrios\Application\UseCases\EliminarBarrio;
use Modules\Barrios\Application\UseCases\ListarBarrios;
use OpenApi\Attributes as OA;

class BarrioController
{
    #[OA\Get(
        path: '/api/v1/barrios',
        summary: 'Listar todos los barrios',
        security: [['bearerAuth' => []]],
        tags: ['Barrios']
    )]
    #[OA\Response(response: 200, description: 'Listado de barrios')]
    public function index(ListarBarrios $useCase)
    {
        try {
            $barrios = $useCase->execute();
            return response()->json(['data' => $barrios], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/barrios',
        summary: 'Crear nuevo barrio',
        security: [['bearerAuth' => []]],
        tags: ['Barrios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre', 'id_comuna'],
                properties: [
                    new OA\Property(property: 'id_comuna', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Poblado')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Barrio creado exitosamente')]
    public function store(Request $request, CrearBarrio $useCase)
    {
        try {
            $barrio = $useCase->execute($request->all());
            return response()->json(['message' => 'Barrio creado exitosamente', 'data' => $barrio], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/barrios/{id}',
        summary: 'Actualizar un barrio',
        security: [['bearerAuth' => []]],
        tags: ['Barrios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del barrio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'id_comuna', type: 'integer', example: 2),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Laureles')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Barrio actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarBarrio $useCase)
    {
        try {
            $barrio = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Barrio actualizado exitosamente', 'data' => $barrio], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/barrios/{id}',
        summary: 'Eliminar un barrio',
        security: [['bearerAuth' => []]],
        tags: ['Barrios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del barrio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Barrio eliminado exitosamente')]
    public function destroy($id, EliminarBarrio $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Barrio eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
