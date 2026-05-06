<?php

namespace Modules\Dashboard\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Dashboard\Application\UseCases\ObtenerDatosDashboard;
use OpenApi\Attributes as OA;

class DashboardController
{
    #[OA\Get(
        path: '/api/v1/dashboard',
        summary: 'Obtener métricas y KPIs del Dashboard',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Datos completos del dashboard estructurados por secciones'
    )]
    public function index(ObtenerDatosDashboard $useCase): JsonResponse
    {
        return response()->json(
            $useCase->execute()
        );
    }

    #[OA\Get(
        path: '/api/v1/dashboard/indicadores',
        summary: 'Obtener indicadores específicos (Agendas, Atendidos, Próximas, Por vencer)',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Indicadores de gestión de visitas',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'total_agendas', type: 'integer', example: 150),
                new OA\Property(property: 'total_atendidos', type: 'integer', example: 80),
                new OA\Property(property: 'total_proximas', type: 'integer', example: 70),
                new OA\Property(property: 'total_a_vencer', type: 'integer', example: 15),
                new OA\Property(
                    property: 'listado_a_vencer', 
                    type: 'array', 
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id_visita', type: 'integer'),
                            new OA\Property(property: 'id_orden_servicio', type: 'integer'),
                            new OA\Property(property: 'fecha_programada', type: 'string', format: 'date'),
                            new OA\Property(property: 'dias_faltantes', type: 'integer')
                        ]
                    )
                )
            ]
        )
    )]
    public function indicadores(\Modules\Dashboard\Application\UseCases\ObtenerDatosDashboardIndicadores $useCase): JsonResponse
    {
        return response()->json(
            $useCase->execute()
        );
    }
}
