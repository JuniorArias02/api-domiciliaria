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
        foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
            if (!empty($filtros[$campo])) {
                $d = \DateTime::createFromFormat('Y-m-d', $filtros[$campo]);
                if (!($d && $d->format('Y-m-d') === $filtros[$campo])) {
                    throw new \InvalidArgumentException("El campo $campo contiene una fecha inválida: '{$filtros[$campo]}'. Debe ser una fecha real (Ej: 2026-02-28).");
                }
            }
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            if (strtotime($filtros['fecha_inicio']) > strtotime($filtros['fecha_fin'])) {
                throw new \InvalidArgumentException("La fecha de inicio no puede ser mayor a la fecha final.");
            }
        }

        return $this->repository->obtenerRutaVisitas($filtros);
    }
}
