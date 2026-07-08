<?php

namespace Modules\Continuidad\Application\UseCases;

use Modules\Continuidad\Domain\Contracts\ContinuidadRepositoryInterface;
use Carbon\Carbon;

class ObtenerAlertasContinuidad
{
    private $repo;

    public function __construct(ContinuidadRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(): array
    {
        $datos = $this->repo->obtenerAlertasContinuidad();
        $resultados = [];
        $hoy = Carbon::today();

        foreach ($datos as $row) {
            $fechaBase = $row->ultima_atencion 
                ? Carbon::parse($row->ultima_atencion)->startOfDay() 
                : Carbon::parse($row->fecha_orden)->startOfDay();
            
            $fechaProyectada = $fechaBase->copy()->addDays((int)$row->frecuencia_dias);
            
            // Retraso positivo = vencido (ya pasó), negativo = aún falta para el ciclo
            $diasRetraso = (int) $fechaProyectada->diffInDays($hoy, false);
            
            $estadoAlerta = 'AGENDADO';

            if ($row->visitas_futuras_count == 0) {
                if ($diasRetraso > 0) {
                    $estadoAlerta = 'VENCIDO';
                } elseif ($diasRetraso >= -5 && $diasRetraso <= 0) {
                    $estadoAlerta = 'POR_VENCER';
                } else {
                    $estadoAlerta = 'AL_DIA_SIN_AGENDAR'; 
                }
            } else {
                $estadoAlerta = 'AGENDADO';
            }

            $resultados[] = [
                'id_paciente' => $row->id_paciente,
                'identificacion' => $row->identificacion,
                'nombre_completo' => $row->nombre_completo,
                'orden_id' => $row->id_orden,
                'especialidad' => $row->especialidad_nombre,
                'frecuencia_dias' => $row->frecuencia_dias,
                'ultima_atencion' => $row->ultima_atencion,
                'fecha_proyectado_nuevo_ciclo' => $fechaProyectada->toDateString(),
                'dias_retraso' => $diasRetraso,
                'estado_alerta' => $estadoAlerta,
            ];
        }

        return $resultados;
    }
}
