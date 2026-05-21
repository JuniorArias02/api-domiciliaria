<?php

namespace Modules\RegistroPrograma\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\RegistroPrograma\Application\UseCases\ObtenerPacientesRegistroPrograma;
use Modules\RegistroPrograma\Application\UseCases\ObtenerAutorizacionesPorPaciente;
use Modules\RegistroPrograma\Application\UseCases\ObtenerOrdenMedicaPorIngreso;
use OpenApi\Attributes as OA;

class RegistroProgramaController
{
    #[OA\Get(
        path: '/api/v1/registro-programa/pacientes',
        summary: 'Obtener pacientes para registro de programa con filtros',
        security: [['bearerAuth' => []]],
        tags: ['RegistroPrograma']
    )]
    #[OA\Parameter(
        name: 'por_pagina',
        description: 'Cantidad de registros por página. Default: 30',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 30, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'pagina',
        description: 'Número de página a consultar. Default: 1',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'nombre_completo',
        description: 'Filtro de búsqueda por nombre completo (parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'Juan Perez')
    )]
    #[OA\Parameter(
        name: 'identificacion',
        description: 'Filtro de búsqueda por identificación (parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: '123456789')
    )]
    #[OA\Parameter(
        name: 'ingreso',
        description: 'Filtro de búsqueda por número de ingreso (parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: '104523')
    )]
    #[OA\Parameter(
        name: 'autorizacion',
        description: 'Filtro de búsqueda por código de autorización (parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'AUT-991283')
    )]
    #[OA\Response(
        response: 200,
        description: 'Listado paginado y filtrado de pacientes del programa',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'meta', type: 'object', properties: [
                        new OA\Property(property: 'pagina_actual', type: 'integer', example: 1),
                        new OA\Property(property: 'por_pagina', type: 'integer', example: 30),
                        new OA\Property(property: 'total', type: 'integer', example: 120),
                        new OA\Property(property: 'ultima_pagina', type: 'integer', example: 4),
                    ]),
                ]
            )
        )
    )]
    public function getPacientes(Request $request, ObtenerPacientesRegistroPrograma $useCase)
    {
        try {
            $porPagina = (int) $request->query('por_pagina', 30);
            $pagina    = (int) $request->query('pagina', 1);

            // Validaciones básicas de paginación
            if ($porPagina < 1) {
                $porPagina = 30;
            }
            if ($pagina < 1) {
                $pagina = 1;
            }

            // Obtener los filtros permitidos
            $filtros = $request->only([
                'nombre_completo',
                'identificacion',
                'ingreso',
                'autorizacion'
            ]);

            $resultado = $useCase->execute($porPagina, $pagina, $filtros);

            return response()->json([
                'data' => $resultado->items(),
                'meta' => [
                    'pagina_actual' => $resultado->currentPage(),
                    'por_pagina'    => $resultado->perPage(),
                    'total'         => $resultado->total(),
                    'ultima_pagina' => $resultado->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/registro-programa/pacientes/{id}/autorizaciones',
        summary: 'Obtener todas las autorizaciones de un paciente',
        security: [['bearerAuth' => []]],
        tags: ['RegistroPrograma']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID del paciente',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Listado de autorizaciones del paciente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'ingreso', type: 'integer', example: 12345),
                                new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date-time', example: '2024-04-28 10:00:00'),
                                new OA\Property(property: 'autorizacion', type: 'string', example: 'AUT-2024-001'),
                                new OA\Property(property: 'estado', type: 'string', example: 'VIGENTE')
                            ]
                        )
                    )
                ]
            )
        )
    )]
    public function getAutorizaciones($id, ObtenerAutorizacionesPorPaciente $useCase)
    {
        try {
            $autorizaciones = $useCase->execute((int)$id);
            return response()->json(['data' => $autorizaciones], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/registro-programa/ingreso/{ingreso}/orden-medica',
        summary: 'Obtener orden médica y servicios con información de visitas por ingreso',
        security: [['bearerAuth' => []]],
        tags: ['RegistroPrograma']
    )]
    #[OA\Parameter(
        name: 'ingreso',
        description: 'Número de ingreso del paciente',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Orden médica y servicios obtenidos correctamente',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))
                ]
            )
        )
    )]
    public function getOrdenMedicaPorIngreso($ingreso, ObtenerOrdenMedicaPorIngreso $useCase)
    {
        try {
            $resultado = $useCase->execute($ingreso);
            return response()->json(['data' => $resultado], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
