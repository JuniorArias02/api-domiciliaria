<?php

namespace Modules\Aseguradoras\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Aseguradoras\Application\UseCases\CrearAseguradora;
use Modules\Aseguradoras\Application\UseCases\ActualizarAseguradora;
use Modules\Aseguradoras\Application\UseCases\EliminarAseguradora;
use Modules\Aseguradoras\Application\UseCases\ListarAseguradoras;
use OpenApi\Attributes as OA;

class AseguradoraController
{
    #[OA\Get(
        path: '/api/v1/aseguradoras',
        summary: 'Listar todas las aseguradoras',
        security: [['bearerAuth' => []]],
        tags: ['Aseguradoras']
    )]
    #[OA\Response(response: 200, description: 'Listado de aseguradoras')]
    public function index(ListarAseguradoras $useCase)
    {
        try {
            $aseguradoras = $useCase->execute();
            return response()->json(['data' => $aseguradoras], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/aseguradoras',
        summary: 'Crear una nueva aseguradora',
        security: [['bearerAuth' => []]],
        tags: ['Aseguradoras']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Aseguradora SURA'),
                    new OA\Property(property: 'codigo_habilitacion', type: 'string', example: 'HAB1234'),
                    new OA\Property(property: 'activa', type: 'integer', example: 1)
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Aseguradora creada exitosamente')]
    public function store(Request $request, CrearAseguradora $useCase)
    {
        try {
            $aseguradora = $useCase->execute($request->all());
            return response()->json(['message' => 'Aseguradora creada exitosamente', 'data' => $aseguradora], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/aseguradoras/{id}',
        summary: 'Actualizar una aseguradora',
        security: [['bearerAuth' => []]],
        tags: ['Aseguradoras']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la aseguradora', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Nueva SURA'),
                    new OA\Property(property: 'activa', type: 'integer', example: 0)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Aseguradora actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarAseguradora $useCase)
    {
        try {
            $aseguradora = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Aseguradora actualizada exitosamente', 'data' => $aseguradora], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/aseguradoras/{id}',
        summary: 'Eliminar una aseguradora',
        security: [['bearerAuth' => []]],
        tags: ['Aseguradoras']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la aseguradora', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Aseguradora eliminada exitosamente')]
    public function destroy($id, EliminarAseguradora $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Aseguradora eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
