<?php

namespace Modules\Pacientes\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Pacientes\Application\UseCases\CrearPaciente;
use Modules\Pacientes\Application\UseCases\ActualizarPaciente;
use Modules\Pacientes\Application\UseCases\EliminarPaciente;
use Modules\Pacientes\Application\UseCases\ActualizarUbicacion;
use Modules\Pacientes\Application\UseCases\ObtenerPacientes;
use OpenApi\Attributes as OA;

class PacienteController
{
    #[OA\Get(
        path: '/api/v1/pacientes',
        summary: 'Listar pacientes con paginación dinámica y filtros',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes']
    )]
    #[OA\Parameter(
        name: 'por_pagina',
        description: 'Cantidad de registros por página (1–100). Default: 15',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)
    )]
    #[OA\Parameter(
        name: 'pagina',
        description: 'Número de página a consultar. Default: 1',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'nombre',
        description: 'Filtra por nombre del paciente (búsqueda parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'Juan')
    )]
    #[OA\Parameter(
        name: 'identificacion',
        description: 'Filtra por número de identificación (búsqueda parcial)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: '1234')
    )]
    #[OA\Parameter(
        name: 'estado',
        description: 'Filtra por estado del paciente',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'activo')
    )]
    #[OA\Parameter(
        name: 'id_aseguradora',
        description: 'Filtra por ID de aseguradora',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Listado paginado de pacientes',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'meta', type: 'object', properties: [
                        new OA\Property(property: 'pagina_actual', type: 'integer', example: 1),
                        new OA\Property(property: 'por_pagina', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 120),
                        new OA\Property(property: 'ultima_pagina', type: 'integer', example: 8),
                    ]),
                ]
            )
        )
    )]
    public function index(Request $request, ObtenerPacientes $useCase)
    {
        try {
            $porPagina  = (int) $request->query('por_pagina', 15);
            $pagina     = (int) $request->query('pagina', 1);
            $filtros    = $request->only(['nombre', 'identificacion', 'estado', 'id_aseguradora']);
            $resultado = $useCase->execute($porPagina, $pagina, $filtros);

            return response()->json([
                'data' => $resultado->items(),
                'meta' => [
                    'pagina_actual' => $resultado->currentPage(),
                    'por_pagina'    => $resultado->perPage(),
                    'total'         => $resultado->total(),
                    'ultima_pagina' => $resultado->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            $status = $e->getCode();
            $status = ($status >= 400 && $status < 600) ? $status : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }


    #[OA\Post(
        path: '/api/v1/pacientes',
        summary: 'Crear un nuevo paciente',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre_completo', 'identificacion', 'sexo', 'tipo_documento', 'fecha_ingreso', 'fecha_nacimiento', 'id_aseguradora', 'direccion'],
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Juan Perez'),
                    new OA\Property(property: 'identificacion', type: 'string', example: '123456789'),
                    new OA\Property(property: 'sexo', type: 'string', example: 'M'),
                    new OA\Property(property: 'tipo_documento', type: 'string', example: 'CC'),
                    new OA\Property(property: 'fecha_ingreso', type: 'string', format: 'date', example: '2026-03-25'),
                    new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1990-01-01'),
                    new OA\Property(property: 'id_aseguradora', type: 'integer', example: 1),
                    new OA\Property(property: 'direccion', type: 'string', example: 'Calle 123 #45-67')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Paciente creado exitosamente')]
    public function store(Request $request, CrearPaciente $useCase)
    {
        try {
            $paciente = $useCase->execute($request->all());
            return response()->json(['message' => 'Paciente creado exitosamente', 'data' => $paciente], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/pacientes/{id}',
        summary: 'Actualizar todos los datos de un paciente',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Juan Perez Actualizado'),
                    new OA\Property(property: 'telefono', type: 'string', example: '3001234567')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Paciente actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarPaciente $useCase)
    {
        try {
            $paciente = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Paciente actualizado exitosamente', 'data' => $paciente], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/pacientes/{id}',
        summary: 'Eliminar un paciente',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Paciente eliminado exitosamente')]
    public function destroy($id, EliminarPaciente $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Paciente eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Patch(
        path: '/api/v1/pacientes/{id}/ubicacion',
        summary: 'Actualizar la ubicación de un paciente (latitud, longitud, url de google maps)',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['latitud', 'longitud', 'url_google_maps'],
                properties: [
                    new OA\Property(property: 'latitud', type: 'number', format: 'float', example: 4.6097100),
                    new OA\Property(property: 'longitud', type: 'number', format: 'float', example: -74.0817500),
                    new OA\Property(property: 'url_google_maps', type: 'string', example: 'https://maps.google.com/?q=4.6097100,-74.0817500')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Ubicación actualizada exitosamente')]
    public function updateUbicacion(Request $request, $id, ActualizarUbicacion $useCase)
    {
        try {
            $paciente = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Ubicación actualizada exitosamente', 'data' => $paciente], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
