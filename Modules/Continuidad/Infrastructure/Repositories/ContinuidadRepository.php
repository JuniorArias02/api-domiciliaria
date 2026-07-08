<?php

namespace Modules\Continuidad\Infrastructure\Repositories;

use Modules\Continuidad\Domain\Contracts\ContinuidadRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ContinuidadRepository implements ContinuidadRepositoryInterface
{
    public function obtenerAlertasContinuidad(): array
    {
        $sql = "
            SELECT 
                p.id_paciente,
                p.identificacion,
                p.nombre_completo,
                om.id_orden,
                os.id_orden_servicio,
                os.frecuencia_dias,
                om.fecha_orden,
                s.nombre_servicio as especialidad_nombre,
                (
                    SELECT MAX(COALESCE(fecha_realizada, fecha_programada)) 
                    FROM visitas_domiciliarias vd 
                    WHERE vd.id_orden_servicio = os.id_orden_servicio 
                      AND vd.estado IN ('REALIZADA', 'PROGRAMADA')
                      AND vd.fecha_programada <= NOW()
                ) as ultima_atencion,
                (
                    SELECT COUNT(*) 
                    FROM visitas_domiciliarias vd2 
                    WHERE vd2.id_orden_servicio = os.id_orden_servicio 
                      AND vd2.estado = 'PROGRAMADA'
                      AND vd2.fecha_programada > NOW()
                ) as visitas_futuras_count
            FROM ordenes_servicios os
            JOIN ordenes_medicas om ON os.id_orden = om.id_orden
            JOIN ingresos i ON om.id_ingreso = i.id_ingreso
            JOIN pacientes p ON i.id_paciente = p.id_paciente
            LEFT JOIN servicios s ON os.id_servicio = s.id_servicio
            WHERE om.estado = 'VIGENTE'
              AND os.frecuencia_dias > 0
        ";

        return DB::select($sql);
    }
}
