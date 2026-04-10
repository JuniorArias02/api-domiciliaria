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
}
