<?php

namespace Modules\Gateway\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Gateway\Application\UseCases\ConsultarDetalleIngreso;
use OpenApi\Attributes as OA;

class GatewayController
{
    #[OA\Get(
        path: '/api/v1/gateway/ingresos/{ingreso}/detalles',
        summary: 'Consultar detalle de un ingreso en Gateway (KubApp)',
        security: [['bearerAuth' => []]],
        tags: ['Gateway']
    )]
    #[OA\Parameter(
        name: 'ingreso',
        description: 'ID entero del ingreso',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Consulta exitosa.',
        content: new OA\JsonContent(
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Parámetros inválidos.'
    )]
    #[OA\Response(
        response: 401,
        description: 'Autenticación fallida.'
    )]
    #[OA\Response(
        response: 404,
        description: 'Ingreso no encontrado.'
    )]
    #[OA\Response(
        response: 500,
        description: 'Error interno o del servicio externo.'
    )]
    public function obtenerDetalleIngreso(Request $request, $ingreso, ConsultarDetalleIngreso $useCase)
    {
        try {
            $data = $useCase->execute($ingreso);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            $status = 400;
            if (str_contains($e->getMessage(), 'no encontrado')) {
                $status = 404;
            } elseif (str_contains($e->getMessage(), 'autenticación')) {
                $status = 401;
            } elseif (str_contains($e->getMessage(), 'Error al consultar')) {
                $status = 500;
            }

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
