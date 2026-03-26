<?php

namespace Modules\Especialidades\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Especialidades\Application\UseCases\CrearEspecialidad;
use Modules\Especialidades\Application\UseCases\ActualizarEspecialidad;
use Modules\Especialidades\Application\UseCases\EliminarEspecialidad;
use Modules\Especialidades\Application\UseCases\ListarEspecialidades;
use OpenApi\Attributes as OA;

class EspecialidadController
{
    #[OA\Get(
        path: '/api/v1/especialidades',
        summary: 'Listar todas las especialidades médicas',
        security: [['bearerAuth' => []]],
        tags: ['Especialidades']
    )]
    #[OA\Response(response: 200, description: 'Listado de especialidades')]
    public function index(ListarEspecialidades $useCase)
    {
        try {
            $especialidades = $useCase->execute();
            return response()->json(['data' => $especialidades], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/especialidades',
        summary: 'Crear nueva especialidad médica',
        security: [['bearerAuth' => []]],
        tags: ['Especialidades']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Fisioterapia'),
                    new OA\Property(property: 'abreviatura', type: 'string', example: 'FISIO')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Especialidad creada exitosamente')]
    public function store(Request $request, CrearEspecialidad $useCase)
    {
        try {
            $especialidad = $useCase->execute($request->all());
            return response()->json(['message' => 'Especialidad creada exitosamente', 'data' => $especialidad], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/especialidades/{id}',
        summary: 'Actualizar una especialidad',
        security: [['bearerAuth' => []]],
        tags: ['Especialidades']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la especialidad', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Fisioterapia Domiciliaria'),
                    new OA\Property(property: 'abreviatura', type: 'string', example: 'FISIO-D')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Especialidad actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarEspecialidad $useCase)
    {
        try {
            $especialidad = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Especialidad actualizada exitosamente', 'data' => $especialidad], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/especialidades/{id}',
        summary: 'Eliminar una especialidad',
        security: [['bearerAuth' => []]],
        tags: ['Especialidades']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la especialidad', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Especialidad eliminada exitosamente')]
    public function destroy($id, EliminarEspecialidad $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Especialidad eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
