<?php

namespace Modules\Continuidad\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Continuidad\Application\UseCases\ObtenerAlertasContinuidad;
use OpenApi\Attributes as OA;

class ContinuidadController
{
    #[OA\Get(
        path: '/api/v1/continuidad/alertas',
        summary: 'Obtener alertas de continuidad de pacientes',
        security: [['bearerAuth' => []]],
        tags: ['Continuidad']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de pacientes con sus proyecciones de agendamiento y alertas'
    )]
    public function obtenerAlertas(ObtenerAlertasContinuidad $useCase)
    {
        try {
            $resultados = $useCase->execute();
            return response()->json(['data' => $resultados], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
