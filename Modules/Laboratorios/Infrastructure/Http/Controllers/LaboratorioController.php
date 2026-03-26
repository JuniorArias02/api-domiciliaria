<?php

namespace Modules\Laboratorios\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Laboratorios\Application\UseCases\CrearLaboratorio;
use Modules\Laboratorios\Application\UseCases\ActualizarLaboratorio;
use Modules\Laboratorios\Application\UseCases\EliminarLaboratorio;
use Modules\Laboratorios\Application\UseCases\ListarLaboratorios;
use OpenApi\Attributes as OA;

class LaboratorioController
{
    #[OA\Get(
        path: '/api/v1/laboratorios',
        summary: 'Listar todas las solicitudes de laboratorio',
        security: [['bearerAuth' => []]],
        tags: ['Laboratorios']
    )]
    #[OA\Response(response: 200, description: 'Listado de laboratorios')]
    public function index(ListarLaboratorios $useCase)
    {
        try {
            $laboratorios = $useCase->execute();
            return response()->json(['data' => $laboratorios], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/laboratorios',
        summary: 'Crear nueva solicitud de laboratorio',
        security: [['bearerAuth' => []]],
        tags: ['Laboratorios']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'fecha_solicitud'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'id_orden_asociada', type: 'integer', example: 4),
                    new OA\Property(property: 'id_personal_toma', type: 'integer', example: 3),
                    new OA\Property(property: 'id_usuario_solicita', type: 'integer', example: 2),
                    new OA\Property(property: 'fecha_solicitud', type: 'string', format: 'date', example: '2026-10-20'),
                    new OA\Property(property: 'fecha_toma_programada', type: 'string', format: 'date-time', example: '2026-10-22 07:00:00'),
                    new OA\Property(property: 'estado', type: 'string', example: 'PENDIENTE'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Toma en ayunas')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Laboratorio creado exitosamente')]
    public function store(Request $request, CrearLaboratorio $useCase)
    {
        try {
            $laboratorio = $useCase->execute($request->all());
            return response()->json(['message' => 'Laboratorio creado exitosamente', 'data' => $laboratorio], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/laboratorios/{id}',
        summary: 'Actualizar una solicitud de laboratorio',
        security: [['bearerAuth' => []]],
        tags: ['Laboratorios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del laboratorio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'estado', type: 'string', example: 'REALIZADO'),
                    new OA\Property(property: 'fecha_toma_real', type: 'string', format: 'date-time', example: '2026-10-22 08:15:00'),
                    new OA\Property(property: 'confirmacion_toma', type: 'integer', example: 1),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Toma realizada sin inconvenientes')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Laboratorio actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarLaboratorio $useCase)
    {
        try {
            $laboratorio = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Laboratorio actualizado exitosamente', 'data' => $laboratorio], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/laboratorios/{id}',
        summary: 'Eliminar una solicitud de laboratorio',
        security: [['bearerAuth' => []]],
        tags: ['Laboratorios']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del laboratorio', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Laboratorio eliminado exitosamente')]
    public function destroy($id, EliminarLaboratorio $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Laboratorio eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
