<?php

namespace Modules\OrdenesMedicas\Application\UseCases;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Exception;

class CrearOrdenMedica
{
    private $repo;

    public function __construct(OrdenMedicaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente']) && empty($data['id_ingreso'])) {
            throw new Exception("El ID del paciente o del ingreso es requerido", 400);
        }
        
        if (empty($data['fecha_orden'])) {
            throw new Exception("El campo fecha_orden es requerido", 400);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // 1. Obtener id_ingreso si no viene
            $idIngreso = $data['id_ingreso'] ?? null;
            if (!$idIngreso) {
                $ingreso = \Illuminate\Support\Facades\DB::table('ingresos')
                    ->where('id_paciente', $data['id_paciente'])
                    ->orderBy('fecha_ingreso', 'desc')
                    ->first();
                
                if (!$ingreso) {
                    throw new Exception("El paciente no tiene un ingreso registrado para crear una orden médica.", 400);
                }
                $idIngreso = $ingreso->id_ingreso;
            }

            // 2. Crear Orden Médica
            $ordenMedica = $this->repo->crear([
                'id_ingreso'   => $idIngreso,
                'fecha_orden'  => $data['fecha_orden'],
                'creado_por'   => $data['creado_por'] ?? auth()->id() ?? 1,
                'observacion'  => $data['observacion'] ?? '',
                'estado'       => $data['estado'] ?? 'VIGENTE'
            ]);

            // 3. Si vienen datos de servicio, crear Orden de Servicio
            if (!empty($data['id_servicio'])) {
                \Illuminate\Support\Facades\DB::table('ordenes_servicios')->insert([
                    'id_orden'                => $ordenMedica->id_orden,
                    'id_servicio'             => $data['id_servicio'],
                    'id_profesional_asignado' => $data['id_personal_ordena'] ?? 0,
                    'numero_sesiones'         => $data['numero_sesiones'] ?? 0,
                    'frecuencia_dias'         => $data['frecuencia_dias'] ?? 0,
                    'estado'                  => 'ACTIVO',
                    'created_at'              => now(),
                    'updated_at'              => now()
                ]);
            }

            return $ordenMedica;
        });
    }
}
