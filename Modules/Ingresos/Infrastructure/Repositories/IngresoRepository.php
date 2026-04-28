<?php

namespace Modules\Ingresos\Infrastructure\Repositories;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Modules\Ingresos\Infrastructure\Models\Ingreso;

class IngresoRepository implements IngresoRepositoryInterface
{
    public function listar()
    {
        return Ingreso::all();
    }

    public function crear(array $data)
    {
        return Ingreso::create($data);
    }
}
