<?php

namespace Modules\PacienteDiagnosticos\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Modules\PacienteDiagnosticos\Application\UseCases\CrearPacienteDiagnostico;
use Modules\PacienteDiagnosticos\Application\UseCases\ActualizarPacienteDiagnostico;
use Modules\PacienteDiagnosticos\Application\UseCases\EliminarPacienteDiagnostico;
use Modules\PacienteDiagnosticos\Application\UseCases\ListarPacienteDiagnosticos;
use OpenApi\Attributes as OA;

class PacienteDiagnosticoController
{
    #[OA\Get(
        path: '/api/v1/paciente-diagnosticos',
        summary: 'Listar todos los diagnósticos de pacientes',
        security: [['bearerAuth' => []]],
        tags: ['Paciente Diagnósticos']
    )]
    #[OA\Response(response: 200, description: 'Listado de diagnósticos')]
    public function index(ListarPacienteDiagnosticos $useCase)
    {
        try {
            $diagnosticos = $useCase->execute();
            return response()->json(['data' => $diagnosticos], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/v1/paciente-diagnosticos',
        summary: 'Asignar nuevo diagnóstico a un paciente',
        security: [['bearerAuth' => []]],
        tags: ['Paciente Diagnósticos']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['id_paciente', 'codigo_cie10', 'id_visita'],
                properties: [
                    new OA\Property(property: 'id_paciente', type: 'integer', example: 1),
                    new OA\Property(property: 'codigo_cie10', type: 'string', example: 'A001'),
                    new OA\Property(property: 'id_visita', type: 'integer', example: 123, description: 'ID de la visita. Use 0 para registros históricos.'),
                    new OA\Property(property: 'tipo_diagnostico', type: 'string', example: 'DOMICILIARIO'),
                    new OA\Property(property: 'es_principal', type: 'integer', example: 1),
                    new OA\Property(property: 'fecha_registro', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'observacion', type: 'string', example: 'Diagnóstico crónico')
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Diagnóstico registrado exitosamente')]
    public function store(Request $request, CrearPacienteDiagnostico $useCase)
    {
        try {
            $diagnostico = $useCase->execute($request->all());
            return response()->json(['message' => 'Diagnóstico registrado exitosamente', 'data' => $diagnostico], 201);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Put(
        path: '/api/v1/paciente-diagnosticos/{id_paciente}/{codigo_cie10}/{tipo_diagnostico}/{id_visita}',
        summary: 'Actualizar un diagnóstico (Llave compuesta)',
        security: [['bearerAuth' => []]],
        tags: ['Paciente Diagnósticos']
    )]
    #[OA\Parameter(name: 'id_paciente', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'codigo_cie10', description: 'Código CIE10', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'tipo_diagnostico', description: 'Tipo Diagnóstico', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'id_visita', description: 'ID de la visita', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'es_principal', type: 'integer', example: 0),
                    new OA\Property(property: 'observacion', type: 'string', example: 'Se modificó la observación')
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Diagnóstico actualizado exitosamente')]
    public function update(Request $request, $id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita, ActualizarPacienteDiagnostico $useCase)
    {
        try {
            $diagnostico = $useCase->execute((int)$id_paciente, $codigo_cie10, $tipo_diagnostico, (int)$id_visita, $request->all());
            return response()->json(['message' => 'Diagnóstico actualizado exitosamente', 'data' => $diagnostico], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Delete(
        path: '/api/v1/paciente-diagnosticos/{id_paciente}/{codigo_cie10}/{tipo_diagnostico}/{id_visita}',
        summary: 'Eliminar un diagnóstico (Llave compuesta)',
        security: [['bearerAuth' => []]],
        tags: ['Paciente Diagnósticos']
    )]
    #[OA\Parameter(name: 'id_paciente', description: 'ID del paciente', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'codigo_cie10', description: 'Código CIE10', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'tipo_diagnostico', description: 'Tipo Diagnóstico', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'id_visita', description: 'ID de la visita', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Diagnóstico eliminado exitosamente')]
    public function destroy($id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita, EliminarPacienteDiagnostico $useCase)
    {
        try {
            $useCase->execute((int)$id_paciente, $codigo_cie10, $tipo_diagnostico, (int)$id_visita);
            return response()->json(['message' => 'Diagnóstico eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 400;
            $status = $status >= 400 && $status < 600 ? $status : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
