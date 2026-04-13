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

            // 1. Crear Orden Médica (Ajustada a tu esquema ENUM y DATE)
            $idOrden = $this->ordenRepository->crearOrden([
                'id_paciente'        => $input->id_paciente,
                'id_especialidad'    => $input->id_especialidad,
                'fecha_orden'        => now()->toDateString(), // Campo obligatorio
                'numero_sesiones'    => $input->numero_sesiones,
                'frecuencia_dias'    => $input->frecuencia_dias,
                'estado'             => 'VIGENTE', // Valor permitido en el ENUM
                'created_at'         => $timestamp,
                'updated_at'         => $timestamp,
            ]);

            // 2. Preparar Visitas
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
