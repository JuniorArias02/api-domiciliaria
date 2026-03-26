<?php

namespace Modules\Comunas\Infrastructure\Repositories;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;
use Modules\Comunas\Infrastructure\Models\Comuna;
use Exception;

class ComunaRepository implements ComunaRepositoryInterface
{
    public function crear(array $data)
    {
        return Comuna::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $comuna = $this->obtenerPorId($id);
        if (!$comuna) {
            throw new Exception("Comuna no encontrada", 404);
        }
        $comuna->update($data);
        return $comuna;
    }

    public function eliminar(int $id)
    {
        $comuna = $this->obtenerPorId($id);
        if (!$comuna) {
            throw new Exception("Comuna no encontrada", 404);
        }
        $comuna->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Comuna::find($id);
    }

    public function listar()
    {
        return Comuna::all();
    }
}
