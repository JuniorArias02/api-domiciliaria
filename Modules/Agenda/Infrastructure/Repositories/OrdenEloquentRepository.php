<?php

declare(strict_types=1);

namespace Modules\Agenda\Infrastructure\Repositories;

use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrdenEloquentRepository implements OrdenRepositoryInterface
{
    public function crearOrden(array $datosOrden): int
    {
        // Usamos Query Builder o un modelo si lo tienes
        // Asegúrate de que la tabla 'ordenes_medicas' exista.
        return DB::table('ordenes_medicas')->insertGetId($datosOrden);
    }

    public function listarPaginado(array $filtros, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = DB::table('ordenes_medicas as o')
            ->select(
                'o.*',
                'p.nombre_completo as nombre_paciente',
                'p.identificacion as identificacion_paciente',
                'e.nombre as nombre_especialidad'
            )
            ->join('pacientes as p', 'o.id_paciente', '=', 'p.id_paciente')
            ->join('especialidades as e', 'o.id_especialidad', '=', 'e.id_especialidad')
            ->orderBy('o.created_at', 'desc');

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('p.nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('p.identificacion', 'like', "%{$buscar}%")
                  ->orWhere('o.numero_mipres', 'like', "%{$buscar}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('o.estado', $filtros['estado']);
        }

        return $query->paginate($perPage);
    }
}
