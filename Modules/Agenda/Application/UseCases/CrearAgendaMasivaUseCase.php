<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Agenda\Application\Contracts\CrearAgendaMasivaUseCaseInterface;
use Modules\Agenda\Application\DTO\CrearAgendaMasivaInputDTO;
use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;
use Modules\Agenda\Domain\Contracts\VisitaRepositoryInterface;
use Exception;

class CrearAgendaMasivaUseCase implements CrearAgendaMasivaUseCaseInterface
{
    public function __construct(
        private readonly OrdenRepositoryInterface $ordenRepository,
        private readonly VisitaRepositoryInterface $visitaRepository
    ) {
    }

    public function execute(CrearAgendaMasivaInputDTO $input): void
    {
        DB::transaction(function () use ($input) {
            $timestamp = now()->toDateTimeString();

            // 1. Obtener el paciente vinculado al ingreso
            $ingreso = DB::table('ingresos')
                ->where('id_ingreso', $input->id_ingreso)
                ->first();

            if (!$ingreso) {
                throw new Exception("El ingreso especificado no existe.");
            }

            // 2. Crear Orden Médica general
            $idOrden = $this->ordenRepository->crearOrden([
                'id_ingreso'  => $ingreso->id_ingreso,
                'fecha_orden' => now()->toDateString(),
                'estado'      => 'VIGENTE',
                'observacion' => $input->observaciones ?? 'Orden masiva generada desde Agenda',
                'created_at'  => $timestamp,
                'updated_at'  => $timestamp,
            ]);

            // 3. Preparar array para inserción masiva de visitas
            $visitasAInsertar = [];

            // 4. Procesar cada servicio
            foreach ($input->servicios as $servicio) {
                // a. Insertar Orden de Servicio
                $idOrdenServicio = DB::table('ordenes_servicios')->insertGetId([
                    'id_orden'                => $idOrden,
                    'id_servicio'             => $servicio->id_servicio,
                    'id_profesional_asignado' => $servicio->id_personal ?? 0,
                    'numero_sesiones'         => $servicio->sesiones,
                    'frecuencia_dias'         => 1, // Por defecto como se acordó
                    'fecha_inicio'            => $servicio->fecha_inicio->toDateTimeString(),
                    'estado'                  => 'ACTIVO',
                    'created_at'              => $timestamp,
                    'updated_at'              => $timestamp,
                ]);

                // b. Generar Visitas Domiciliarias para este servicio
                for ($i = 0; $i < $servicio->sesiones; $i++) {
                    // Calculamos la fecha programada sumando los días (frecuencia = 1)
                    $fechaProgramada = $servicio->fecha_inicio->copy()->addDays($i);

                    $visitasAInsertar[] = [
                        'id_orden_servicio' => $idOrdenServicio,
                        'id_paciente'       => $ingreso->id_paciente,
                        'id_personal'       => $servicio->id_personal ?? 0,
                        'fecha_programada'  => $fechaProgramada->toDateTimeString(),
                        'estado'            => 'PROGRAMADA',
                        'created_at'        => $timestamp,
                        'updated_at'        => $timestamp,
                    ];
                }
            }

            // 5. Insertar todas las visitas de todos los servicios de forma masiva
            if (!empty($visitasAInsertar)) {
                $this->visitaRepository->insertarMasivamente($visitasAInsertar);
            }
        });
    }
}
