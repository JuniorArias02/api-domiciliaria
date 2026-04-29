<?php

namespace Modules\Municipios\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Municipios\Application\UseCases\ListarMunicipios;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Municipios', description: 'Endpoints para la gestión de municipios')]
class MunicipioController extends Controller
{
    #[OA\Get(
        path: '/api/v1/municipios',
        summary: 'Listar todos los municipios',
        security: [['bearerAuth' => []]],
        tags: ['Municipios']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de municipios obtenida correctamente'
    )]
    public function index(ListarMunicipios $useCase)
    {
        try {
            $municipios = $useCase->execute();
            return response()->json(['data' => $municipios], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
