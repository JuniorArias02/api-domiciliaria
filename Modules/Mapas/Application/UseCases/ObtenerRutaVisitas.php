<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class ObtenerRutaVisitas
{
    private $repository;

    public function __construct(MapaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filtros)
    {
        // Validar rango de fechas (fecha inicial debe ser menor o igual a fecha final)
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            if (strtotime($filtros['fecha_inicio']) > strtotime($filtros['fecha_fin'])) {
                throw new \InvalidArgumentException("La fecha de inicio no puede ser mayor a la fecha final.");
            }
        }

        return $this->repository->obtenerRutaVisitas($filtros);
    }
}
