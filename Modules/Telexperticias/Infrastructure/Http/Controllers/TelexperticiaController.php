<?php

namespace Modules\Telexperticias\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Telexperticias\Application\UseCases\CrearTelexperticia;
use Modules\Telexperticias\Application\UseCases\ActualizarTelexperticia;
use Modules\Telexperticias\Application\UseCases\EliminarTelexperticia;
use Modules\Telexperticias\Application\UseCases\ListarTelexperticias;
use OpenApi\Attributes as OA;

class TelexperticiaController
{
    #[OA\Get(
        path: '/api/v1/telexperticias',
        summary: 'Listar todas las telexperticias',
        security: [['bearerAuth' => []]],
        tags: ['Telexperticias']
    )]
    #[OA\Response(response: 200, description: 'Listado de telexperticias')]
    public function index(ListarTelexperticias $useCase)
    {
        try {
            $telexperticias = $useCase->execute();
            return response()->json(['data' => $telexperticias], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/telexperticias',
        summary: 'Crear nueva telexperticia',
        security: [['bearerAuth' => []]],
        tags: ['Telexperticias']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'id_especialidad', 'fecha_solicitud'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'id_especialidad', type: 'integer', example: 3),
                    new OA\Property(property: 'id_usuario_solicita', type: 'integer', example: 2),
                    new OA\Property(property: 'fecha_solicitud', type: 'string', format: 'date', example: '2026-11-05'),
                    new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 15),
                    new OA\Property(property: 'estado', type: 'string', example: 'SOLICITADA'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Consulta de seguimiento por especialista en neurología')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Telexperticia creada exitosamente')]
    public function store(Request $request, CrearTelexperticia $useCase)
    {
        try {
            $telexperticia = $useCase->execute($request->all());
            return response()->json(['message' => 'Telexperticia creada exitosamente', 'data' => $telexperticia], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/telexperticias/{id}',
        summary: 'Actualizar una telexperticia',
        security: [['bearerAuth' => []]],
        tags: ['Telexperticias']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la telexperticia', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'estado', type: 'string', example: 'REALIZADA'),
                    new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 30),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Especialista recomienda continuar tratamiento')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Telexperticia actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarTelexperticia $useCase)
    {
        try {
            $telexperticia = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Telexperticia actualizada exitosamente', 'data' => $telexperticia], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/telexperticias/{id}',
        summary: 'Eliminar una telexperticia',
        security: [['bearerAuth' => []]],
        tags: ['Telexperticias']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la telexperticia', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Telexperticia eliminada exitosamente')]
    public function destroy($id, EliminarTelexperticia $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Telexperticia eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
