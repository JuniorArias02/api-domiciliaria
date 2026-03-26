<?php

namespace Modules\SolicitudesEquipos\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\SolicitudesEquipos\Application\UseCases\CrearSolicitudEquipo;
use Modules\SolicitudesEquipos\Application\UseCases\ActualizarSolicitudEquipo;
use Modules\SolicitudesEquipos\Application\UseCases\EliminarSolicitudEquipo;
use Modules\SolicitudesEquipos\Application\UseCases\ListarSolicitudesEquipos;
use OpenApi\Attributes as OA;

class SolicitudEquipoController
{
    #[OA\Get(
        path: '/api/v1/solicitudes-equipos',
        summary: 'Listar todas las solicitudes de equipos',
        security: [['bearerAuth' => []]],
        tags: ['Solicitudes Equipos']
    )]
    #[OA\Response(response: 200, description: 'Listado de solicitudes')]
    public function index(ListarSolicitudesEquipos $useCase)
    {
        try {
            $solicitudes = $useCase->execute();
            return response()->json(['data' => $solicitudes], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/solicitudes-equipos',
        summary: 'Crear nueva solicitud de equipo',
        security: [['bearerAuth' => []]],
        tags: ['Solicitudes Equipos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'modalidad', type: 'string', example: 'ALQUILER'),
                    new OA\Property(property: 'tiempo_requerido', type: 'string', example: '1 MES'),
                    new OA\Property(property: 'estado', type: 'string', example: 'PENDIENTE'),
                    new OA\Property(property: 'fecha_solicitud', type: 'string', format: 'date', example: '2026-06-01'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Se necesita oxigeno urgente')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Solicitud creada exitosamente')]
    public function store(Request $request, CrearSolicitudEquipo $useCase)
    {
        try {
            $solicitud = $useCase->execute($request->all());
            return response()->json(['message' => 'Solicitud creada exitosamente', 'data' => $solicitud], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/solicitudes-equipos/{id}',
        summary: 'Actualizar una solicitud de equipo',
        security: [['bearerAuth' => []]],
        tags: ['Solicitudes Equipos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la solicitud', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'estado', type: 'string', example: 'APROBADO'),
                    new OA\Property(property: 'id_usuario_gestiona', type: 'integer', example: 2),
                    new OA\Property(property: 'fecha_entrega', type: 'string', format: 'date', example: '2026-06-05')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Solicitud actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarSolicitudEquipo $useCase)
    {
        try {
            $solicitud = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Solicitud actualizada exitosamente', 'data' => $solicitud], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/solicitudes-equipos/{id}',
        summary: 'Eliminar una solicitud de equipo',
        security: [['bearerAuth' => []]],
        tags: ['Solicitudes Equipos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la solicitud', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Solicitud eliminada exitosamente')]
    public function destroy($id, EliminarSolicitudEquipo $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Solicitud eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
