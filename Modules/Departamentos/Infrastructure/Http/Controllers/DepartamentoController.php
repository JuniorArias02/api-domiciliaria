<?php

namespace Modules\Departamentos\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Departamentos\Application\UseCases\ListarDepartamentos;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Departamentos', description: 'Endpoints para la gestión de departamentos')]
class DepartamentoController extends Controller
{
    #[OA\Get(
        path: '/api/v1/departamentos',
        summary: 'Listar todos los departamentos',
        security: [['bearerAuth' => []]],
        tags: ['Departamentos']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de departamentos obtenida correctamente'
    )]
    public function index(ListarDepartamentos $useCase)
    {
        try {
            $departamentos = $useCase->execute();
            return response()->json(['data' => $departamentos], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
