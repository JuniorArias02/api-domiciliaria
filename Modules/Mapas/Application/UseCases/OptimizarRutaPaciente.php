<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

/**
 * Caso de Uso: OptimizarRutaPaciente
 * 
 * Este caso de uso implementa una lógica de optimización "Ciega al Profesional",
 * agrupando a todos los pacientes que requieren visita en un mes específico.
 * Utiliza la lógica del algoritmo de proximidad global (Script Python).
 */
class OptimizarRutaPaciente
{
    private $repository;

    public function __construct(MapaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Ejecuta la optimización global.
     * 
     * @param array $filtros ['mes' => int, 'anio' => int]
     * @return array
     */
    public function execute(array $filtros)
    {
        return $this->repository->optimizarRutasGlobales($filtros);
    }
}
