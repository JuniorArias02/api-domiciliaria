<?php

namespace Modules\DetalleSolicitudEquipos\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\DetalleSolicitudEquipos\Application\UseCases\CrearDetalleSolicitudEquipo;
use Modules\DetalleSolicitudEquipos\Application\UseCases\ActualizarDetalleSolicitudEquipo;
use Modules\DetalleSolicitudEquipos\Application\UseCases\EliminarDetalleSolicitudEquipo;
use Modules\DetalleSolicitudEquipos\Application\UseCases\ListarDetallesSolicitudEquipos;
use OpenApi\Attributes as OA;

class DetalleSolicitudEquipoController
{
    #[OA\Get(
        path: '/api/v1/detalle-solicitudes-equipos',
        summary: 'Listar todos los detalles de solicitudes de equipos',
        security: [['bearerAuth' => []]],
        tags: ['Detalle Solicitudes Equipos']
    )]
    #[OA\Response(response: 200, description: 'Listado de detalles')]
    public function index(ListarDetallesSolicitudEquipos $useCase)
    {
        try {
            $detalles = $useCase->execute();
            return response()->json(['data' => $detalles], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/detalle-solicitudes-equipos',
        summary: 'Crear nuevo detalle para solicitud',
        security: [['bearerAuth' => []]],
        tags: ['Detalle Solicitudes Equipos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_solicitud', 'id_equipo'],
                properties: [
                    new OA\Property(property: 'id_solicitud', type: 'integer', example: 1),
                    new OA\Property(property: 'id_equipo', type: 'integer', example: 5),
                    new OA\Property(property: 'cantidad', type: 'integer', example: 2),
                    new OA\Property(property: 'observacion', type: 'string', example: 'Cama hospitalaria bimanual')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Detalle creado exitosamente')]
    public function store(Request $request, CrearDetalleSolicitudEquipo $useCase)
    {
        try {
            $detalle = $useCase->execute($request->all());
            return response()->json(['message' => 'Detalle creado exitosamente', 'data' => $detalle], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/detalle-solicitudes-equipos/{id}',
        summary: 'Actualizar un detalle',
        security: [['bearerAuth' => []]],
        tags: ['Detalle Solicitudes Equipos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del detalle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'cantidad', type: 'integer', example: 3),
                    new OA\Property(property: 'observacion', type: 'string', example: 'Se incrementa la cantidad requerida')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Detalle actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarDetalleSolicitudEquipo $useCase)
    {
        try {
            $detalle = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Detalle actualizado exitosamente', 'data' => $detalle], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/detalle-solicitudes-equipos/{id}',
        summary: 'Eliminar un detalle',
        security: [['bearerAuth' => []]],
        tags: ['Detalle Solicitudes Equipos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del detalle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Detalle eliminado exitosamente')]
    public function destroy($id, EliminarDetalleSolicitudEquipo $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Detalle eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
