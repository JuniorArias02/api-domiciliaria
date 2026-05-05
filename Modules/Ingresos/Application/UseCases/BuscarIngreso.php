<?php

declare(strict_types=1);

namespace Modules\Ingresos\Application\UseCases;

use Illuminate\Support\Facades\DB;

class BuscarIngreso
{
    /**
     * Busca ingresos exclusivamente por número de ingreso.
     *
     * @param string $busqueda
     * @return \Illuminate\Support\Collection
     */
    public function execute(string $busqueda)
    {
        return DB::table('ingresos')
            ->leftJoin('pacientes', 'ingresos.id_paciente', '=', 'pacientes.id_paciente')
            ->select(
                'ingresos.id_ingreso',
                'ingresos.ingreso',
                'ingresos.id_paciente',
                'ingresos.autorizacion',
                'ingresos.fecha_ingreso',
                'pacientes.identificacion',
                'pacientes.nombre_completo'
            )
            ->where('ingresos.ingreso', 'like', "%{$busqueda}%")
            ->limit(20)
            ->get();
    }
}
