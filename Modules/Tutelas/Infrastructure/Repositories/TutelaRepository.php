<?php

namespace Modules\Tutelas\Infrastructure\Repositories;

use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;
use Modules\Tutelas\Infrastructure\Models\Tutela;
use Exception;

class TutelaRepository implements TutelaRepositoryInterface
{
    public function crear(array $data)
    {
        return Tutela::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $tutela = $this->obtenerPorId($id);
        if (!$tutela) {
            throw new Exception("Tutela no encontrada", 404);
        }
        $tutela->update($data);
        return $tutela;
    }

    public function eliminar(int $id)
    {
        $tutela = $this->obtenerPorId($id);
        if (!$tutela) {
            throw new Exception("Tutela no encontrada", 404);
        }
        $tutela->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Tutela::find($id);
    }

    public function listar()
    {
        return Tutela::all();
    }
}
