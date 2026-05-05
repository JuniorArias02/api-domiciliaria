<?php

namespace Modules\OrdenesMedicas\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\OrdenesMedicas\Application\UseCases\CrearOrdenMedica;
use Modules\OrdenesMedicas\Application\UseCases\ActualizarOrdenMedica;
use Modules\OrdenesMedicas\Application\UseCases\EliminarOrdenMedica;
use Modules\OrdenesMedicas\Application\UseCases\ListarOrdenesMedicas;
use Modules\OrdenesMedicas\Application\UseCases\ObtenerOrdenesPorIngreso;
use OpenApi\Attributes as OA;

class OrdenMedicaController
{
    #[OA\Get(
        path: '/api/v1/ordenes-medicas',
        summary: 'Listar todas las ordenes médicas',
        security: [['bearerAuth' => []]],
        tags: ['Ordenes Médicas']
    )]
    #[OA\Response(response: 200, description: 'Listado de órdenes médicas')]
    public function index(ListarOrdenesMedicas $useCase)
    {
        try {
            $ordenes = $useCase->execute();
            return response()->json(['data' => $ordenes], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Get(
        path: '/api/v1/ordenes-medicas/ingreso/{ingreso}',
        summary: 'Obtener órdenes médicas por número de ingreso',
        security: [['bearerAuth' => []]],
        tags: ['Ordenes Médicas'],
        parameters: [
            new OA\Parameter(
                name: 'ingreso',
                in: 'path',
                required: true,
                description: 'Número del ingreso (campo ingreso)',
                schema: new OA\Schema(type: 'integer')
            )
        ]
    )]
    #[OA\Response(response: 200, description: 'Órdenes obtenidas correctamente')]
    public function porIngreso($ingreso, ObtenerOrdenesPorIngreso $useCase)
    {
        try {
            $ordenes = $useCase->execute((int)$ingreso);
            return response()->json(['data' => $ordenes], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/ordenes-medicas',
        summary: 'Crear nueva orden médica',
        security: [['bearerAuth' => []]],
        tags: ['Ordenes Médicas']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'id_servicio', 'fecha_orden'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'id_servicio', type: 'integer', example: 2),
                    new OA\Property(property: 'id_personal_ordena', type: 'integer', example: 3),
                    new OA\Property(property: 'fecha_orden', type: 'string', format: 'date', example: '2026-06-01'),
                    new OA\Property(property: 'numero_sesiones', type: 'integer', example: 10),
                    new OA\Property(property: 'frecuencia_dias', type: 'integer', example: 2),
                    new OA\Property(property: 'numero_mipres', type: 'string', example: 'MP-987654321'),
                    new OA\Property(property: 'observacion', type: 'string', example: 'Terapia física a domicilio'),
                    new OA\Property(property: 'estado', type: 'string', example: 'VIGENTE')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Orden médica creada exitosamente')]
    public function store(Request $request, CrearOrdenMedica $useCase)
    {
        try {
            $orden = $useCase->execute($request->all());
            return response()->json(['message' => 'Orden médica creada exitosamente', 'data' => $orden], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/ordenes-medicas/{id}',
        summary: 'Actualizar una orden médica',
        security: [['bearerAuth' => []]],
        tags: ['Ordenes Médicas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la orden médica', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'estado', type: 'string', example: 'EJECUTADA'),
                    new OA\Property(property: 'numero_sesiones', type: 'integer', example: 12)
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Orden médica actualizada exitosamente')]
    public function update(Request $request, $id, ActualizarOrdenMedica $useCase)
    {
        try {
            $orden = $useCase->execute((int)$id, $request->all());
            return response()->json(['message' => 'Orden médica actualizada exitosamente', 'data' => $orden], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/ordenes-medicas/{id}',
        summary: 'Eliminar una orden médica',
        security: [['bearerAuth' => []]],
        tags: ['Ordenes Médicas']
    )]
    #[OA\Parameter(name: 'id', description: 'ID de la orden médica', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Orden médica eliminada exitosamente')]
    public function destroy($id, EliminarOrdenMedica $useCase)
    {
        try {
            $useCase->execute((int)$id);
            return response()->json(['message' => 'Orden médica eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
