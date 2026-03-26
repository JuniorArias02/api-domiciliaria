<?php

namespace Modules\Tutelas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Tutelas\Application\UseCases\CrearTutela;
use Modules\Tutelas\Application\UseCases\ActualizarTutela;
use Modules\Tutelas\Application\UseCases\EliminarTutela;
use Modules\Tutelas\Application\UseCases\ListarTutelas;
use OpenApi\Attributes as OA;

class TutelaController
{
    #[OA\Get(
        path: '/api/v1/tutelas',
        summary: 'Listar todas las tutelas',
        security: [['bearerAuth' => []]],
        tags: ['Tutelas']
    )]
    #[OA\Response(response: 200, description: 'Listado de tutelas')]
    public function index(ListarTutelas $useCase)
    {
        try {
            $tutelas = $useCase->execute();
            return response()->json(['data' => $tutelas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/tutelas',
        summary: 'Crear nueva tutela',
        security: [['bearerAuth' => []]],
        tags: ['Tutelas']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'numero_tutela'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'numero_tutela', type: 'string', example: 'T-1234567'),
                    new OA\Property(property: 'fecha_tutela', type: 'string', format: 'date', example: '2026-03-25'),
                    new OA\Property(property: 'prestacion_autorizada', type: 'integer', example: 1),
                    new OA\Property(property: 'es_permanente', type: 'integer', example: 0),
                    new OA\Property(property: 'duracion_dias', type: 'integer', example: 30),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Requiere atención especial')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Tutela creada exitosamente')]
    public function store(Request $request, CrearTutela $useCase)
    {
        try {
            $tutela = $useCase->execute($request->all());
            return response()->json(['message' => 'Tutela creada exitosamente', 'data' => $tutela], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/tutelas/{id}',
        summary: 'Actualizar una tutela',
        security: [['bearerAuth' => []]],
        tags: ['Tutelas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la tutela', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'prestacion_autorizada', type: 'integer', example: 1),
                    new OA\Property(property: 'duracion_dias', type: 'integer', example: 60)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Tutela actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarTutela $useCase)
    {
        try {
            $tutela = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Tutela actualizada exitosamente', 'data' => $tutela], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/tutelas/{id}',
        summary: 'Eliminar una tutela',
        security: [['bearerAuth' => []]],
        tags: ['Tutelas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la tutela', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Tutela eliminada exitosamente')]
    public function destroy($id, EliminarTutela $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Tutela eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
