<?php

namespace Modules\Comunas\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Comunas\Application\UseCases\ListarComunas;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comunas', description: 'Endpoints para la gestión de comunas')]
class ComunaController extends Controller
{
    #[OA\Get(
        path: '/api/v1/comunas',
        summary: 'Listar todas las comunas',
        security: [['bearerAuth' => []]],
        tags: ['Comunas']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de comunas obtenida correctamente'
    )]
    public function index(ListarComunas $useCase)
    {
        try {
            $comunas = $useCase->execute();
            return response()->json(['data' => $comunas], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
