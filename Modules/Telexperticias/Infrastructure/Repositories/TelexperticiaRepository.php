<?php

namespace Modules\Telexperticias\Infrastructure\Repositories;

use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;
use Modules\Telexperticias\Infrastructure\Models\Telexperticia;
use Exception;

class TelexperticiaRepository implements TelexperticiaRepositoryInterface
{
    public function crear(array $data)
    {
        return Telexperticia::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $telexperticia = $this->obtenerPorId($id);
        if (!$telexperticia) {
            throw new Exception("Telexperticia no encontrada", 404);
        }
        $telexperticia->update($data);
        return $telexperticia;
    }

    public function eliminar(int $id)
    {
        $telexperticia = $this->obtenerPorId($id);
        if (!$telexperticia) {
            throw new Exception("Telexperticia no encontrada", 404);
        }
        $telexperticia->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Telexperticia::find($id);
    }

    public function listar()
    {
        return Telexperticia::all();
    }
}
