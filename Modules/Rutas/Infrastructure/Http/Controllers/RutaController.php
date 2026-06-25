<?php

namespace Modules\Rutas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Rutas\Application\UseCases\CrearRuta;
use Modules\Rutas\Application\UseCases\ObtenerRuta;
use Modules\Rutas\Application\UseCases\ListarRutas;
use Modules\Rutas\Application\UseCases\ExportarRutasExcel;
use Modules\Rutas\Application\UseCases\EditarRuta;
use Modules\Rutas\Application\UseCases\EliminarRuta;
use Modules\Rutas\Application\UseCases\AsignarRuta;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;

class RutaController
{
    #[OA\Get(
        path: '/api/v1/rutas',
        summary: 'Listar todas las rutas',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Response(response: 200, description: 'Listado de todas las rutas')]
    public function index(ListarRutas $useCase)
    {
        try {
            $rutas = $useCase->execute();
            return response()->json(['data' => $rutas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
    #[OA\Post(
        path: '/api/v1/rutas',
        summary: 'Crear una nueva ruta',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_personal', 'fecha_ruta'],
                properties: [
                    new OA\Property(property: 'id_personal', type: 'integer', example: 1, description: 'ID del profesional de la salud asignado'),
                    new OA\Property(property: 'fecha_ruta', type: 'string', format: 'date', example: '2026-05-25', description: 'Fecha en la que se realizará la ruta'),
                    new OA\Property(property: 'estado', type: 'string', example: 'EN_DISENO', description: 'Estado inicial de la ruta'),
                    new OA\Property(
                        property: 'visitas',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_visita', type: 'integer', example: 3627),
                                new OA\Property(property: 'orden_visita', type: 'integer', example: 1)
                            ],
                            type: 'object'
                        ),
                        description: 'Listado de visitas asociadas a la ruta con su respectivo orden de recorrido'
                    )
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Ruta creada exitosamente')]
    #[OA\Response(response: 400, description: 'Datos de entrada inválidos')]
    #[OA\Response(response: 404, description: 'Profesional no encontrado')]
    public function store(Request $request, CrearRuta $useCase)
    {
        try {
            $ruta = $useCase->execute($request->all());
            return response()->json(['message' => 'Ruta creada exitosamente', 'data' => $ruta], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/rutas/{id}',
        summary: 'Obtener el detalle de una ruta',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la ruta', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Detalle de la ruta con profesional y visitas asociadas')]
    #[OA\Response(response: 404, description: 'Ruta no encontrada')]
    public function show($id, ObtenerRuta $useCase)
    {
        try {
            $ruta = $useCase->execute((int)$id);
            return response()->json(['data' => $ruta], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
    #[OA\Get(
        path: '/api/v1/rutas/exportar/excel',
        summary: 'Exportar las rutas a un archivo Excel',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Archivo Excel con las rutas',
        content: new OA\MediaType(
            mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            schema: new OA\Schema(type: 'string', format: 'binary')
        )
    )]
    #[OA\Response(response: 400, description: 'Error al exportar las rutas')]
    public function exportExcel(ExportarRutasExcel $useCase)
    {
        try {
            $export = $useCase->execute();
            return Excel::download($export, 'rutas_' . date('Y_m_d_H_i_s') . '.xlsx');
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/rutas/{id}',
        summary: 'Editar una ruta existente',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la ruta a editar', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'id_personal', type: 'integer', example: 1),
                    new OA\Property(property: 'fecha_ruta', type: 'string', format: 'date', example: '2026-05-25'),
                    new OA\Property(property: 'estado', type: 'string', example: 'EN_DISENO'),
                    new OA\Property(
                        property: 'visitas',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_visita', type: 'integer', example: 3627),
                                new OA\Property(property: 'orden_visita', type: 'integer', example: 1)
                            ],
                            type: 'object'
                        )
                    )
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Ruta editada exitosamente')]
    public function update(Request $request, $id, EditarRuta $useCase)
    {
        try {
            $ruta = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Ruta editada exitosamente', 'data' => $ruta], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/rutas/{id}',
        summary: 'Eliminar una ruta',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la ruta a eliminar', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Ruta eliminada exitosamente')]
    public function destroy($id, EliminarRuta $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Ruta eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Patch(
        path: '/api/v1/rutas/{id}/asignar',
        summary: 'Asignar una ruta para enfatizar que está lista para ejecutarse',
        security: [['bearerAuth' => []]],
        tags: ['Rutas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la ruta', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Estado de ruta cambiado a ASIGNADA')]
    public function asignar($id, AsignarRuta $useCase)
    {
        try {
            $ruta = $useCase->execute((int)$id);
            return response()->json(['message' => 'Ruta asignada exitosamente', 'data' => $ruta], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
