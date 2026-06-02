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

    public function obtenerPorAutorizacion(string $autorizacion)
    {
        return OrdenServicio::select('ordenes_servicios.*')
            ->join('ordenes_medicas', 'ordenes_servicios.id_orden', '=', 'ordenes_medicas.id_orden')
            ->join('ingresos', 'ordenes_medicas.id_ingreso', '=', 'ingresos.id_ingreso')
            ->where('ingresos.autorizacion', $autorizacion)
            ->with(['servicio', 'visitas' => function ($query) {
                $query->whereNotIn('estado', ['CANCELADA', 'NO_ATENDIDA']);
            }])
            ->get()
            ->map(function ($orden) {
                $orden->sesiones_realizadas = $orden->visitas->count();
                $orden->sesiones_pendientes = max(0, $orden->numero_sesiones - $orden->sesiones_realizadas);
                return $orden;
            })
            ->filter(function ($orden) {
                return $orden->sesiones_pendientes > 0;
            })->values();
    }
}

