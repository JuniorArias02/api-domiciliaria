<?php

namespace Modules\Servicios\Infrastructure\Repositories;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Modules\Servicios\Infrastructure\Models\Servicio;

class ServicioRepository implements ServicioRepositoryInterface
{
    /**
     * @param array $data
     * @return Servicio
     */
    public function crear(array $data)
    {
        return Servicio::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Servicio|null
     */
    public function actualizar(int $id, array $data)
    {
        $servicio = $this->obtenerPorId($id);
        if ($servicio) {
            $servicio->update($data);
            return $servicio;
        }
        return null;
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function eliminar(int $id)
    {
        $servicio = $this->obtenerPorId($id);
        if ($servicio) {
            return $servicio->delete();
        }
        return false;
    }

    /**
     * @param int $id
     * @return Servicio|null
     */
    public function obtenerPorId(int $id)
    {
        return Servicio::find($id);
    }

    public function listar()
    {
        return Servicio::all();
    }
}
