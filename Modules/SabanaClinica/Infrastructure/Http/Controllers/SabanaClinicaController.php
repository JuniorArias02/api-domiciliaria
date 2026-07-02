<?php

namespace Modules\SabanaClinica\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\SabanaClinica\Application\UseCases\ObtenerSabanaUseCase;
use Modules\SabanaClinica\Application\UseCases\ObtenerFilaSabanaUseCase;
use Modules\SabanaClinica\Application\UseCases\ActualizarColumnaSabanaUseCase;
use Modules\SabanaClinica\Application\UseCases\CrearFilaSabanaUseCase;
use Modules\SabanaClinica\Application\UseCases\EliminarFilaSabanaUseCase;
use Modules\SabanaClinica\Infrastructure\Http\Requests\ActualizarColumnaRequest;
use Modules\SabanaClinica\Infrastructure\Http\Requests\CrearFilaRequest;
use OpenApi\Attributes as OA;

class SabanaClinicaController
{
    #[OA\Get(
        path: '/api/v1/sabana-clinica',
        summary: 'Obtener datos para la grilla Excel (Sábana Clínica)',
        security: [['bearerAuth' => []]],
        tags: ['Sabana Clinica']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Retorna JSON estructurado con columnas y filas.'
    )]
    public function index(Request $request, ObtenerSabanaUseCase $useCase)
    {
        try {
            $filtros = $request->only(['search', 'estado']);
            $perPage = $request->input('per_page', 50);
            
            $data = $useCase->execute($filtros, (int) $perPage);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/sabana-clinica/{id}',
        summary: 'Obtener una sola fila de la Sábana',
        security: [['bearerAuth' => []]],
        tags: ['Sabana Clinica']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Retorna los datos de un paciente y sus relaciones como fila.'
    )]
    public function show(int $id, ObtenerFilaSabanaUseCase $useCase)
    {
        try {
            $data = $useCase->execute($id);
            if (!$data) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/sabana-clinica',
        summary: 'Crear un nuevo registro (Fila) en la Sábana',
        security: [['bearerAuth' => []]],
        tags: ['Sabana Clinica']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['identificacion', 'nombre_y_apellido'],
                properties: [
                    new OA\Property(property: 'tipo_documento', type: 'string', example: 'CC'),
                    new OA\Property(property: 'identificacion', type: 'string', example: '123456789'),
                    new OA\Property(property: 'nombre_y_apellido', type: 'string', example: 'Juan Perez'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201, 
        description: 'Registro creado exitosamente'
    )]
    public function store(CrearFilaRequest $request, CrearFilaSabanaUseCase $useCase)
    {
        try {
            $id = $useCase->execute($request->validated());
            return response()->json(['message' => 'Creado exitosamente', 'id' => $id], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Patch(
        path: '/api/v1/sabana-clinica/{id}',
        summary: 'Actualizar una celda específica de la fila',
        security: [['bearerAuth' => []]],
        tags: ['Sabana Clinica']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['field', 'value'],
                properties: [
                    new OA\Property(property: 'field', type: 'string', example: 'telefono'),
                    new OA\Property(property: 'value', type: 'string', example: '3001234567'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200, 
        description: 'Celda actualizada'
    )]
    public function update(int $id, ActualizarColumnaRequest $request, ActualizarColumnaSabanaUseCase $useCase)
    {
        try {
            $useCase->execute($id, $request->validated());
            return response()->json(['message' => 'Actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Delete(
        path: '/api/v1/sabana-clinica/{id}',
        summary: 'Eliminar una fila (borrado lógico)',
        security: [['bearerAuth' => []]],
        tags: ['Sabana Clinica']
    )]
    #[OA\Response(
        response: 200, 
        description: 'Fila eliminada/inactivada'
    )]
    public function destroy(int $id, EliminarFilaSabanaUseCase $useCase)
    {
        try {
            $useCase->execute($id);
            return response()->json(['message' => 'Eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
