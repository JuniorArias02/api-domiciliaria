<?php

namespace Modules\Ingresos\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ingresos\Application\UseCases\CrearIngreso;
use Modules\Ingresos\Application\UseCases\ListarIngresos;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Ingresos', description: 'Endpoints para la gestión de ingresos de pacientes')]
class IngresoController extends Controller
{
    #[OA\Get(
        path: '/api/v1/ingresos',
        summary: 'Listar todos los ingresos',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos obtenida correctamente'
    )]
    public function index(ListarIngresos $useCase)
    {
        try {
            $ingresos = $useCase->execute();
            return response()->json(['data' => $ingresos], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ingresos',
        summary: 'Registrar un nuevo ingreso',
        security: [['bearerAuth' => []]],
        tags: ['Ingresos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['ingreso', 'id_paciente', 'fecha_ingreso'],
                properties: [
                    new OA\Property(property: 'ingreso', type: 'integer', example: 12345),
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date-time', example: '2024-04-28 10:00:00')
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Ingreso registrado correctamente'
    )]
    #[OA\Response(
        response: 400,
        description: 'Error en la solicitud'
    )]
    public function store(Request $request, CrearIngreso $useCase)
    {
        try {
            $nuevoIngreso = $useCase->execute($request->all());
            return response()->json(['data' => $nuevoIngreso], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
