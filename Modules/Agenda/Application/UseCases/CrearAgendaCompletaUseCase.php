<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Agenda\Application\Contracts\CrearAgendaCompletaUseCaseInterface;
use Modules\Agenda\Application\DTO\AgendaInputDTO;
use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;
use Modules\Agenda\Domain\Contracts\VisitaRepositoryInterface;

class CrearAgendaCompletaUseCase implements CrearAgendaCompletaUseCaseInterface
{
    public function __construct(
        private readonly OrdenRepositoryInterface $ordenRepository,
        private readonly VisitaRepositoryInterface $visitaRepository
    ) {
    }

    public function execute(AgendaInputDTO $input): void
    {
        DB::transaction(function () use ($input) {
            $timestamp = now()->toDateTimeString();

            // 1. Obtener el ingreso activo del paciente
            $ingreso = DB::table('ingresos')
                ->where('id_paciente', $input->id_paciente)
                ->orderBy('fecha_ingreso', 'desc')
                ->first();

            if (!$ingreso) {
                throw new \Exception("El paciente no tiene un ingreso registrado para crear una orden.");
            }

            // 2. Crear Orden Médica (Nueva estructura)
            $idOrden = $this->ordenRepository->crearOrden([
                'id_ingreso'   => $ingreso->id_ingreso,
                'fecha_orden'  => now()->toDateString(),
                'estado'       => 'VIGENTE',
                'observacion'  => 'Generada automáticamente desde Agenda',
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
            ]);

            // 3. Crear Orden de Servicio (Donde ahora reside la frecuencia y sesiones)
            DB::table('ordenes_servicios')->insert([
                'id_orden'                => $idOrden,
                'id_servicio'             => $input->id_especialidad, // Mapeamos especialidad a servicio
                'id_profesional_asignado' => $input->id_personal ?? 0,
                'numero_sesiones'         => $input->numero_sesiones,
                'frecuencia_dias'         => $input->frecuencia_dias,
                'estado'                  => 'ACTIVO',
                'created_at'              => $timestamp,
                'updated_at'              => $timestamp,
            ]);

            // 4. Preparar Visitas
            $visitasAInsertar = [];
            
            for ($i = 0; $i < $input->numero_sesiones; $i++) {
                $fechaProgramada = $input->fecha_inicio->copy()->addDays($i * $input->frecuencia_dias);

                $visitasAInsertar[] = [
                    'id_orden_asociada' => $idOrden,
                    'id_paciente'       => $input->id_paciente,
                    'id_personal'       => $input->id_personal ?? 0,
                    'id_especialidad'   => $input->id_especialidad,
                    'fecha_programada'  => $fechaProgramada->toDateTimeString(),
                    'estado'            => 'PROGRAMADA',
                    'created_at'        => $timestamp,
                    'updated_at'        => $timestamp,
                ];
            }

            $this->visitaRepository->insertarMasivamente($visitasAInsertar);
        });
    }
}
