<?php

namespace Modules\Pacientes\Infrastructure\Repositories;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;
use Modules\Pacientes\Infrastructure\Models\Paciente;
use Exception;

class PacienteRepository implements PacienteRepositoryInterface
{
    public function crear(array $data)
    {
        return Paciente::create($data);
    }

    public function actualizar(int $id, array $data)
    {
        $paciente = $this->obtenerPorId($id);
        if (!$paciente) {
            throw new Exception("Paciente no encontrado", 404);
        }
        $paciente->update($data);
        return $paciente;
    }

    public function eliminar(int $id)
    {
        $paciente = $this->obtenerPorId($id);
        if (!$paciente) {
            throw new Exception("Paciente no encontrado", 404);
        }
        $paciente->delete();
        return true;
    }

    public function obtenerPorId(int $id)
    {
        return Paciente::find($id);
    }
}
