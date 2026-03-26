<?php

namespace Modules\Comunas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Comunas\Application\UseCases\CrearComuna;
use Modules\Comunas\Application\UseCases\ActualizarComuna;
use Modules\Comunas\Application\UseCases\EliminarComuna;
use Modules\Comunas\Application\UseCases\ListarComunas;
use OpenApi\Attributes as OA;

class ComunaController
{
    #[OA\Get(
        path: '/api/v1/comunas',
        summary: 'Listar todas las comunas',
        security: [['bearerAuth' => []]],
        tags: ['Comunas']
    )]
    #[OA\Response(response: 200, description: 'Listado de comunas')]
    public function index(ListarComunas $useCase)
    {
        try {
            $comunas = $useCase->execute();
            return response()->json(['data' => $comunas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/comunas',
        summary: 'Crear nueva comuna',
        security: [['bearerAuth' => []]],
        tags: ['Comunas']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre', 'id_zona'],
                properties: [
                    new OA\Property(property: 'id_zona', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Comuna 1')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Comuna creada exitosamente')]
    public function store(Request $request, CrearComuna $useCase)
    {
        try {
            $comuna = $useCase->execute($request->all());
            return response()->json(['message' => 'Comuna creada exitosamente', 'data' => $comuna], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/comunas/{id}',
        summary: 'Actualizar una comuna',
        security: [['bearerAuth' => []]],
        tags: ['Comunas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la comuna', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'id_zona', type: 'integer', example: 2),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Comuna 2')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Comuna actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarComuna $useCase)
    {
        try {
            $comuna = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Comuna actualizada exitosamente', 'data' => $comuna], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/comunas/{id}',
        summary: 'Eliminar una comuna',
        security: [['bearerAuth' => []]],
        tags: ['Comunas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la comuna', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Comuna eliminada exitosamente')]
    public function destroy($id, EliminarComuna $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Comuna eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
