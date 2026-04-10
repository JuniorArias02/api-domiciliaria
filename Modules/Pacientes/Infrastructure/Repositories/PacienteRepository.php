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

    public function obtenerPaginado(int $porPagina, int $pagina, array $filtros = [])
    {
        $query = Paciente::query();

        // Cargar relaciones para obtener nombres
        $query->with(['aseguradora', 'madrina', 'barrio']);

        // Filtro por nombre (búsqueda parcial, insensible a mayúsculas)
        if (!empty($filtros['nombre'])) {
            $query->where('nombre_completo', 'like', '%' . $filtros['nombre'] . '%');
        }

        // Filtro por número de identificación (búsqueda parcial)
        if (!empty($filtros['identificacion'])) {
            $query->where('identificacion', 'like', '%' . $filtros['identificacion'] . '%');
        }

        // Filtro por estado (active, inactive, etc.)
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        // Filtro por aseguradora (ID exacto)
        if (!empty($filtros['id_aseguradora'])) {
            $query->where('id_aseguradora', (int) $filtros['id_aseguradora']);
        }

        return $query
            ->orderBy('nombre_completo', 'asc')
            ->paginate(perPage: $porPagina, page: $pagina);
    }
}
