<?php

namespace Modules\Cuidadores\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cuidadores\Application\UseCases\CrearCuidador;
use Modules\Cuidadores\Application\UseCases\ActualizarCuidador;
use Modules\Cuidadores\Application\UseCases\EliminarCuidador;
use Modules\Cuidadores\Application\UseCases\ListarCuidadores;
use OpenApi\Attributes as OA;

class CuidadorController
{
    #[OA\Get(
        path: '/api/v1/cuidadores',
        summary: 'Listar todos los cuidadores',
        security: [['bearerAuth' => []]],
        tags: ['Cuidadores']
    )]
    #[OA\Response(response: 200, description: 'Listado de cuidadores')]
    public function index(ListarCuidadores $useCase)
    {
        try {
            $cuidadores = $useCase->execute();
            return response()->json(['data' => $cuidadores], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/cuidadores',
        summary: 'Crear nuevo cuidador',
        security: [['bearerAuth' => []]],
        tags: ['Cuidadores']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre_completo', 'id_paciente'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Andrea Gomez'),
                    new OA\Property(property: 'parentesco', type: 'string', example: 'Hija'),
                    new OA\Property(property: 'telefono', type: 'string', example: '3009998877'),
                    new OA\Property(property: 'email', type: 'string', example: 'andrea@ejemplo.com'),
                    new OA\Property(property: 'es_principal', type: 'integer', example: 1),
                    new OA\Property(property: 'tipo_auxiliar', type: 'string', example: 'Enfermera'),
                    new OA\Property(property: 'horas_diarias', type: 'integer', example: 8)
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Cuidador creado exitosamente')]
    public function store(Request $request, CrearCuidador $useCase)
    {
        try {
            $cuidador = $useCase->execute($request->all());
            return response()->json(['message' => 'Cuidador creado exitosamente', 'data' => $cuidador], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/cuidadores/{id}',
        summary: 'Actualizar un cuidador',
        security: [['bearerAuth' => []]],
        tags: ['Cuidadores']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del cuidador', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'telefono', type: 'string', example: '3105554433'),
                    new OA\Property(property: 'es_principal', type: 'integer', example: 0)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Cuidador actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarCuidador $useCase)
    {
        try {
            $cuidador = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Cuidador actualizado exitosamente', 'data' => $cuidador], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/cuidadores/{id}',
        summary: 'Eliminar un cuidador',
        security: [['bearerAuth' => []]],
        tags: ['Cuidadores']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del cuidador', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Cuidador eliminado exitosamente')]
    public function destroy($id, EliminarCuidador $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Cuidador eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
