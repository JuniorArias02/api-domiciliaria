<?php

declare(strict_types=1);

namespace Modules\Agenda\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Modules\Agenda\Application\Contracts\CrearAgendaCompletaUseCaseInterface;
use Modules\Agenda\Application\Contracts\ListarAgendasPaginadasUseCaseInterface;
use Modules\Agenda\Application\DTO\AgendaInputDTO;
use Modules\Agenda\Application\DTO\PaginacionAgendaInputDTO;
use Modules\Agenda\Domain\Exceptions\FrecuenciaInvalidaException;
use OpenApi\Attributes as OA;
use Exception;

class AgendaController
{
    #[OA\Get(
        path: '/api/v1/agenda/listado',
        summary: 'Obtener listado de órdenes/agendas con paginación dinámica',
        security: [['bearerAuth' => []]],
        tags: ['Agenda']
    )]
    #[OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15))]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'buscar', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'estado', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Listado paginado de agendas')]
    public function index(Request $request, ListarAgendasPaginadasUseCaseInterface $useCase): JsonResponse
    {
        try {
            $input = PaginacionAgendaInputDTO::fromRequest($request->all());
            $paginacion = $useCase->execute($input);

            return response()->json([
                'success' => true,
                'data'    => $paginacion->items(),
                'meta'    => [
                    'total'        => $paginacion->total(),
                    'per_page'     => $paginacion->perPage(),
                    'current_page' => $paginacion->currentPage(),
                    'last_page'    => $paginacion->lastPage(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/v1/agenda/crear',
        summary: 'Punto de entrada para programar masivamente todas las visitas derivadas de una orden',
        security: [['bearerAuth' => []]],
        tags: ['Agenda']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['id_paciente', 'id_especialidad', 'numero_sesiones', 'frecuencia_dias', 'fecha_inicio'],
            properties: [
                new OA\Property(property: 'id_paciente', type: 'integer'),
                new OA\Property(property: 'id_especialidad', type: 'integer'),
                new OA\Property(property: 'numero_sesiones', type: 'integer', description: 'Número total de visitas a programar.'),
                new OA\Property(property: 'frecuencia_dias', type: 'integer', description: 'Separación en días entre cada visita.'),
                new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date-time', description: 'Fecha de la primera sesión (YYYY-MM-DD HH:mm:ss).'),
                new OA\Property(property: 'id_personal', type: 'integer', nullable: true, description: 'ID opcional del profesional asignado desde el inicio.')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Agenda creada exitosamente')]
    #[OA\Response(response: 422, description: 'Datos incompletos o errores de validación')]
    public function crearAgendaCompleta(Request $request, CrearAgendaCompletaUseCaseInterface $useCase): JsonResponse
    {
        try {
            // Se realiza validación básica en la capa de http para evitar datos mal formados,
            // complementando la lógica de negocio.
            $data = $request->validate([
                'id_paciente'     => 'required|integer',
                'id_especialidad' => 'required|integer',
                'numero_sesiones' => 'required|integer|min:1',
                'frecuencia_dias' => 'required|integer|min:0',
                'fecha_inicio'    => 'required|date',
                'id_personal'     => 'nullable|integer',
            ]);

            // Se arma y carga el DTO usando la promoción al vuelo 
            $inputDto = new AgendaInputDTO(
                id_paciente: (int) $data['id_paciente'],
                id_especialidad: (int) $data['id_especialidad'],
                numero_sesiones: (int) $data['numero_sesiones'],
                frecuencia_dias: (int) $data['frecuencia_dias'],
                fecha_inicio: Carbon::parse($data['fecha_inicio']),
                id_personal: isset($data['id_personal']) ? (int) $data['id_personal'] : null
            );

            // Se ejecuta la lógica de Negocio orquestada por el Caso de Uso (DB Transaction)
            $useCase->execute($inputDto);

            return response()->json([
                'success' => true,
                'message' => "Agenda completada satisfactoriamente con {$data['numero_sesiones']} sesiones."
            ], 201);

        } catch (FrecuenciaInvalidaException | \InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Errores de validación',
                'details' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            // DB Transaction lanza una Exception genérica al finalizar el rollback 
            return response()->json([
                'success' => false,
                'error'   => 'Error interno al procesar la agenda: ' . $e->getMessage()
            ], 500); 
        }
    }
}
