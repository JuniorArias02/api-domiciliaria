<?php

namespace Modules\Personal\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Personal\Application\UseCases\CrearPersonal;
use Modules\Personal\Application\UseCases\ActualizarPersonal;
use Modules\Personal\Application\UseCases\EliminarPersonal;
use Modules\Personal\Application\UseCases\ListarPersonal;
use Modules\Personal\Application\UseCases\BuscarPersonalUseCase;
use Modules\Personal\Application\UseCases\ObtenerEstadisticasCumplimientoUseCase;
use Modules\Personal\Application\UseCases\ObtenerIngresosInvolucradosUseCase;
use OpenApi\Attributes as OA;

class PersonalController
{
    #[OA\Get(
        path: '/api/v1/personal',
        summary: 'Listar todo el personal',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\Response(response: 200, description: 'Listado de personal')]
    public function index(ListarPersonal $useCase)
    {
        try {
            $personal = $useCase->execute();
            return response()->json(['data' => $personal], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/personal',
        summary: 'Crear nuevo personal',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['nombre_completo', 'numero_documento', 'tipo_documento', 'id_cargo'],
                properties: [
                    new OA\Property(property: 'id_cargo', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Maria Rodriguez'),
                    new OA\Property(property: 'numero_documento', type: 'string', example: '1020304050'),
                    new OA\Property(property: 'tipo_documento', type: 'string', example: 'CC'),
                    new OA\Property(property: 'tarjeta_profesional', type: 'string', example: 'TP-98765'),
                    new OA\Property(property: 'telefono', type: 'string', example: '3109876543'),
                    new OA\Property(property: 'email', type: 'string', example: 'maria@ejemplo.com'),
                    new OA\Property(property: 'estado', type: 'integer', example: 1)
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Personal creado exitosamente')]
    public function store(Request $request, CrearPersonal $useCase)
    {
        try {
            $personal = $useCase->execute($request->all());
            return response()->json(['message' => 'Personal creado exitosamente', 'data' => $personal], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/personal/{id}',
        summary: 'Actualizar datos de personal',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del personal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'telefono', type: 'string', example: '3201112233'),
                    new OA\Property(property: 'estado', type: 'integer', example: 0)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Personal actualizado exitosamente')]
    public function update(Request $request, $id, ActualizarPersonal $useCase)
    {
        try {
            $personal = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Personal actualizado exitosamente', 'data' => $personal], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/personal/{id}',
        summary: 'Eliminar personal',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del personal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Personal eliminado exitosamente')]
    public function destroy($id, EliminarPersonal $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Personal eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/personal/buscar',
        summary: 'Buscar personal por nombre o número de documento',
        security: [['bearerAuth' => []]],
        tags: ['Personal'],
        parameters: [
            new OA\Parameter(name: 'q', description: 'Cadena de búsqueda', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'limit', description: 'Número máximo de resultados (por defecto 5)', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 5))
        ]
    )]
    #[OA\Response(
        response: 200, 
        description: 'Resultados de la búsqueda',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_personal', type: 'integer', example: 1),
                                new OA\Property(property: 'id_cargo', type: 'integer', example: 1),
                                new OA\Property(property: 'nombre_completo', type: 'string', example: 'Maria Rodriguez'),
                                new OA\Property(property: 'numero_documento', type: 'string', example: '1020304050'),
                                new OA\Property(property: 'tipo_documento', type: 'string', example: 'CC'),
                                new OA\Property(property: 'tarjeta_profesional', type: 'string', example: 'TP-98765'),
                                new OA\Property(property: 'telefono', type: 'string', example: '3109876543'),
                                new OA\Property(property: 'email', type: 'string', example: 'maria@ejemplo.com'),
                                new OA\Property(property: 'estado', type: 'integer', example: 1)
                            ]
                        )
                    )
                ]
            )
        )
    )]
    public function search(Request $request, BuscarPersonalUseCase $useCase)
    {
        try {
            $query = $request->query('q', '');
            $limit = (int) $request->query('limit', 5);
            $personal = $useCase->execute($query, $limit);
            return response()->json(['data' => $personal], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/personal/{id}/estadisticas',
        summary: 'Obtener estadísticas de cumplimiento de un profesional',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del personal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Estadísticas de cumplimiento'
    )]
    public function getEstadisticas($id, ObtenerEstadisticasCumplimientoUseCase $useCase)
    {
        try {
            $estadisticas = $useCase->execute((int)$id);
            return response()->json(['data' => $estadisticas], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/personal/{id}/ingresos',
        summary: 'Obtener ingresos en los que el profesional se encuentra involucrado',
        security: [['bearerAuth' => []]],
        tags: ['Personal']
    )]
    #[OA\Parameter(name: 'id', description: 'ID del personal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos con información asociada'
    )]
    public function getIngresosInvolucrados($id, ObtenerIngresosInvolucradosUseCase $useCase)
    {
        try {
            $ingresos = $useCase->execute((int)$id);
            return response()->json(['data' => $ingresos], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
