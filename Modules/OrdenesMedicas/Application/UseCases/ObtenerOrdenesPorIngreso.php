<?php

namespace Modules\OrdenesMedicas\Application\UseCases;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;

class ObtenerOrdenesPorIngreso
{
    private $repo;

    public function __construct(OrdenMedicaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $numeroIngreso)
    {
        return $this->repo->obtenerPorNumeroIngreso($numeroIngreso);
    }
}
