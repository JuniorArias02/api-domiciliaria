<?php

namespace Modules\VisitasDomiciliarias\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\VisitasDomiciliarias\Application\UseCases\CrearVisitaDomiciliaria;
use Modules\VisitasDomiciliarias\Application\UseCases\ActualizarVisitaDomiciliaria;
use Modules\VisitasDomiciliarias\Application\UseCases\EliminarVisitaDomiciliaria;
use Modules\VisitasDomiciliarias\Application\UseCases\ListarVisitasDomiciliarias;
use OpenApi\Attributes as OA;

class VisitaDomiciliariaController
{
    #[OA\Get(
        path: '/api/v1/visitas-domiciliarias',
        summary: 'Listar todas las visitas domiciliarias',
        security: [['bearerAuth' => []]],
        tags: ['Visitas Domiciliarias']
    )]
    #[OA\Response(response: 200, description: 'Listado de visitas')]
    public function index(ListarVisitasDomiciliarias $useCase)
    {
        try {
            $visitas = $useCase->execute();
            return response()->json(['data' => $visitas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/visitas-domiciliarias',
        summary: 'Programar nueva visita domiciliaria',
        security: [['bearerAuth' => []]],
        tags: ['Visitas Domiciliarias']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'id_personal', 'id_orden_servicio', 'fecha_programada'],
                properties: [
                    new OA\Property(property: 'id_orden_servicio', type: 'integer', example: 2),
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 5),
                    new OA\Property(property: 'id_personal', type: 'integer', example: 3),
                    new OA\Property(property: 'id_usuario_programa', type: 'integer', example: 1),
                    new OA\Property(property: 'fecha_programada', type: 'string', format: 'date-time', example: '2026-10-15 08:30:00'),
                    new OA\Property(property: 'estado', type: 'string', example: 'PROGRAMADA'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Verificar acceso en porteria')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Visita programada exitosamente')]
    public function store(Request $request, CrearVisitaDomiciliaria $useCase)
    {
        try {
            $visita = $useCase->execute($request->all());
            return response()->json(['message' => 'Visita programada exitosamente', 'data' => $visita], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/visitas-domiciliarias/{id}',
        summary: 'Actualizar una visita domiciliaria (Check-in/Check-out o Cancelación)',
        security: [['bearerAuth' => []]],
        tags: ['Visitas Domiciliarias']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la visita', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'estado', type: 'string', example: 'REALIZADA'),
                    new OA\Property(property: 'fecha_realizada', type: 'string', format: 'date-time', example: '2026-10-15 09:45:00'),
                    new OA\Property(property: 'latitud_checkin', type: 'number', format: 'float', example: 6.2442),
                    new OA\Property(property: 'longitud_checkin', type: 'number', format: 'float', example: -75.5812),
                    new OA\Property(property: 'motivo_cancelacion', type: 'string', example: 'Paciente no se encontraba en el domicilio')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Visita actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarVisitaDomiciliaria $useCase)
    {
        try {
            $visita = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Visita actualizada exitosamente', 'data' => $visita], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/visitas-domiciliarias/{id}',
        summary: 'Eliminar una visita domiciliaria',
        security: [['bearerAuth' => []]],
        tags: ['Visitas Domiciliarias']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la visita', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Visita eliminada exitosamente')]
    public function destroy($id, EliminarVisitaDomiciliaria $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Visita eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
