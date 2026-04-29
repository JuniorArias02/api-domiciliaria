<?php

namespace Modules\Barrios\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Barrios\Application\UseCases\ListarBarrios;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Barrios', description: 'Endpoints para la gestión de barrios')]
class BarrioController extends Controller
{
    #[OA\Get(
        path: '/api/v1/barrios',
        summary: 'Listar todos los barrios',
        security: [['bearerAuth' => []]],
        tags: ['Barrios']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de barrios obtenida correctamente'
    )]
    public function index(ListarBarrios $useCase)
    {
        try {
            $barrios = $useCase->execute();
            return response()->json(['data' => $barrios], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
