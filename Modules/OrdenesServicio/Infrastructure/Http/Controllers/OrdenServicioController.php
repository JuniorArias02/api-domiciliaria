<?php

namespace Modules\OrdenesServicio\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OrdenesServicio\Application\UseCases\CrearOrdenServicio;
use Modules\OrdenesServicio\Application\UseCases\ListarOrdenesServicio;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'OrdenesServicio', description: 'Endpoints para la gestión de órdenes de servicios')]
class OrdenServicioController extends Controller
{
    #[OA\Get(
        path: '/api/v1/ordenes-servicio',
        summary: 'Listar todas las órdenes de servicio',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de órdenes de servicio obtenida correctamente'
    )]
    public function index(ListarOrdenesServicio $useCase)
    {
        try {
            $ordenes = $useCase->execute();
            return response()->json(['data' => $ordenes], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ordenes-servicio',
        summary: 'Registrar una nueva orden de servicio',
        security: [['bearerAuth' => []]],
        tags: ['OrdenesServicio']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_orden', 'id_servicio', 'numero_sesiones'],
                properties: [
                    new OA\Property(property: 'id_orden', type: 'integer', example: 1),
                    new OA\Property(property: 'id_servicio', type: 'integer', example: 5),
                    new OA\Property(property: 'id_profesional_asignado', type: 'integer', example: 10),
                    new OA\Property(property: 'numero_sesiones', type: 'integer', example: 10),
                    new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 2),
                    new OA\Property(property: 'estado', type: 'string', example: 'PROGRAMADA')
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Orden de servicio registrada correctamente'
    )]
    public function store(Request $request, CrearOrdenServicio $useCase)
    {
        try {
            $nuevaOrden = $useCase->execute($request->all());
            return response()->json(['data' => $nuevaOrden], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
