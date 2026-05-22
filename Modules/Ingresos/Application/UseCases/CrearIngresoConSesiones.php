<?php

declare(strict_types=1);

namespace Modules\Ingresos\Application\UseCases;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class CrearIngresoConSesiones
{
    private $ingresoRepo;
    private $ordenMedicaRepo;
    private $ordenServicioRepo;
    private $visitaRepo;

    public function __construct(
        IngresoRepositoryInterface $ingresoRepo,
        OrdenMedicaRepositoryInterface $ordenMedicaRepo,
        OrdenServicioRepositoryInterface $ordenServicioRepo,
        VisitaDomiciliariaRepositoryInterface $visitaRepo
    ) {
        $this->ingresoRepo = $ingresoRepo;
        $this->ordenMedicaRepo = $ordenMedicaRepo;
        $this->ordenServicioRepo = $ordenServicioRepo;
        $this->visitaRepo = $visitaRepo;
    }

    public function execute(array $data, int $usuarioId): array
    {
        // 1. Validaciones básicas
        if (empty($data['id_paciente'])) {
            throw new Exception("El ID del paciente es requerido");
        }

        if (empty($data['autorizacion'])) {
            throw new Exception("La autorización es requerida");
        }

        if (empty($data['servicios']) || !is_array($data['servicios'])) {
            throw new Exception("Se requiere al menos un servicio en el listado");
        }

        // Validar que no haya servicios duplicados
        $idServicios = [];
        foreach ($data['servicios'] as $servicioData) {
            if (empty($servicioData['id_servicio'])) {
                throw new Exception("El ID del servicio es requerido en cada elemento");
            }
            $idServicios[] = $servicioData['id_servicio'];
        }

        if (count($idServicios) !== count(array_unique($idServicios))) {
            throw new Exception("No se permite registrar el mismo servicio duplicado en la orden médica");
        }

        return DB::transaction(function () use ($data, $usuarioId) {
            // 2. Generar el siguiente número de ingreso secuencial
            $siguienteIngreso = $this->ingresoRepo->obtenerSiguienteNumeroIngreso();

            // 3. Crear Ingreso
            $ingreso = $this->ingresoRepo->crear([
                'ingreso' => $siguienteIngreso,
                'id_paciente' => $data['id_paciente'],
                'autorizacion' => $data['autorizacion'],
                'fecha_ingreso' => now()->toDateTimeString()
            ]);

            // 4. Crear Orden Médica
            $ordenMedica = $this->ordenMedicaRepo->crear([
                'id_ingreso' => $ingreso->id_ingreso,
                'creado_por' => $usuarioId,
                'fecha_orden' => now()->toDateString(),
                'observacion' => $data['observacion'] ?? null,
                'estado' => 'VIGENTE'
            ]);

            $totalServicios = count($data['servicios']);
            $serviciosCreados = [];
            $visitasCreadas = [];

            // 5. Crear Órdenes de Servicio y sus Visitas Domiciliarias correspondientes
            foreach ($data['servicios'] as $index => $servicioData) {
                if (empty($servicioData['id_servicio'])) {
                    throw new Exception("El ID del servicio es requerido en cada elemento");
                }
                if (empty($servicioData['id_profesional'])) {
                    throw new Exception("El ID del profesional asignado es requerido en cada servicio");
                }
                if (!isset($servicioData['numero_sesiones'])) {
                    throw new Exception("El número de sesiones es requerido en cada servicio");
                }
                if (!isset($servicioData['frecuencia_dias'])) {
                    throw new Exception("La frecuencia en días es requerida en cada servicio");
                }
                if (empty($servicioData['fecha_inicio'])) {
                    throw new Exception("La fecha de inicio es requerida en cada servicio");
                }

                // Crear Orden de Servicio
                $ordenServicio = $this->ordenServicioRepo->crear([
                    'id_orden' => $ordenMedica->id_orden,
                    'id_servicio' => $servicioData['id_servicio'],
                    'id_profesional_asignado' => $servicioData['id_profesional'],
                    'numero_sesiones' => $servicioData['numero_sesiones'],
                    'frecuencia_dias' => $servicioData['frecuencia_dias'],
                    'fecha_inicio' => $servicioData['fecha_inicio'],
                    'estado' => 'PROGRAMADA'
                ]);

                $serviciosCreados[] = $ordenServicio;

                // Determinar codigo_ingreso único para evitar colisiones de Unique Index
                $codigoIngreso = $totalServicios === 1 
                    ? (string) $siguienteIngreso 
                    : $siguienteIngreso . '-' . ($index + 1);

                // Crear Visita Domiciliaria (primera visita de servicio)
                $visita = $this->visitaRepo->crear([
                    'codigo_ingreso' => $codigoIngreso,
                    'id_orden_servicio' => $ordenServicio->id_orden_servicio,
                    'id_paciente' => $data['id_paciente'],
                    'id_personal' => $servicioData['id_profesional'],
                    'fecha_programada' => $servicioData['fecha_inicio'],
                    'id_usuario_programa' => $usuarioId,
                    'estado' => 'PROGRAMADA'
                ]);

                $visitasCreadas[] = $visita;
            }

            return [
                'ingreso' => $ingreso,
                'orden_medica' => $ordenMedica,
                'ordenes_servicios' => $serviciosCreados,
                'visitas_domiciliarias' => $visitasCreadas
            ];
        });
    }
}
