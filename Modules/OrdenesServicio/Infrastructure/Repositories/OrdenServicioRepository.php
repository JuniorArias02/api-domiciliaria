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

    public function actualizar(int $id, array $data)
    {
        $ordenServicio = $this->obtenerPorId($id);
        if (!$ordenServicio) {
            throw new \Exception("Orden de servicio no encontrada", 404);
        }
        $ordenServicio->update($data);
        return $ordenServicio;
    }

    public function obtenerPorId(int $id)
    {
        return OrdenServicio::find($id);
    }
}

