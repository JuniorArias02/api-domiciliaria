<?php

declare(strict_types=1);

namespace Modules\Servicios\Application\UseCases;

use Illuminate\Support\Facades\DB;

class BuscarServicio
{
    /**
     * Busca servicios por código o por nombre de servicio.
     *
     * @param string $busqueda
     * @return \Illuminate\Support\Collection
     */
    public function execute(string $busqueda)
    {
        return DB::table('servicios')
            ->select(
                'id_servicio',
                'codigo_servicio',
                'nombre_servicio',
                'descripcion',
                'estado'
            )
            ->where('codigo_servicio', 'like', "%{$busqueda}%")
            ->orWhere('nombre_servicio', 'like', "%{$busqueda}%")
            ->limit(20)
            ->get();
    }
}
