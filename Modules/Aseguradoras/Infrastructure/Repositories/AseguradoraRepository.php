<?php

namespace Modules\Aseguradoras\Infrastructure\Repositories;

use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Modules\Aseguradoras\Infrastructure\Models\Aseguradora;
use Exception;

class AseguradoraRepository implements AseguradoraRepositoryInterface
{
    public function crear(array $data)
    {
        return Aseguradora::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $aseguradora = $this->obtenerPorId($id);
        if (!$aseguradora) {
            throw new Exception("Aseguradora no encontrada", 404);
        }
        $aseguradora->update($data);
        return $aseguradora;
    }

    public function eliminar(int $id)
    {
        $aseguradora = $this->obtenerPorId($id);
        if (!$aseguradora) {
            throw new Exception("Aseguradora no encontrada", 404);
        }
        $aseguradora->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Aseguradora::find($id);
    }

    public function listar()
    {
        return Aseguradora::all();
    }
}
