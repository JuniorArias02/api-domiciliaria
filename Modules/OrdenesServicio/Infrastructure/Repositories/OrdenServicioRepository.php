<?php

namespace Modules\OrdenesServicio\Infrastructure\Repositories;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Modules\OrdenesServicio\Infrastructure\Models\OrdenServicio;

class OrdenServicioRepository implements OrdenServicioRepositoryInterface
{
    public function listar()
    {
        return OrdenServicio::all();
    }

    public function crear(array $data)
    {
        return OrdenServicio::create($data);
    }
}
