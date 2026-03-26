<?php

namespace Modules\Cargos\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cargos\Application\UseCases\CrearCargo;
use Modules\Cargos\Application\UseCases\ActualizarCargo;
use Modules\Cargos\Application\UseCases\EliminarCargo;
use Modules\Cargos\Application\UseCases\ListarCargos;
use OpenApi\Attributes as OA;

class CargoController
{
    #[OA\Get(
        path: '/api/v1/cargos',
        summary: 'Listar todos los cargos',
        security: [['bearerAuth' => []]],
        tags: ['Cargos']
    )]
    #[OA\Response(response: 200, description: 'Listado de cargos')]
    public function index(ListarCargos $useCase)
    {
        try {
            $cargos = $useCase->execute();
            return response()->json(['data' => $cargos], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/cargos',
        summary: 'Crear un nuevo cargo',
        security: [['bearerAuth' => []]],
        tags: ['Cargos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Administrador')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Cargo creado exitosamente')]
    public function store(Request $request, CrearCargo $useCase)
    {
        try {
            $cargo = $useCase->execute($request->all());
            return response()->json(['message' => 'Cargo creado exitosamente', 'data' => $cargo], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/cargos/{id}',
        summary: 'Actualizar un cargo',
        security: [['bearerAuth' => []]],
        tags: ['Cargos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del cargo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Supervisor')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Cargo actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarCargo $useCase)
    {
        try {
            $cargo = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Cargo actualizado exitosamente', 'data' => $cargo], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/cargos/{id}',
        summary: 'Eliminar un cargo',
        security: [['bearerAuth' => []]],
        tags: ['Cargos']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del cargo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Cargo eliminado exitosamente')]
    public function destroy($id, EliminarCargo $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Cargo eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
