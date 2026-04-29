<?php

namespace Modules\Ingresos\Application\UseCases;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;

class ObtenerAutorizacionesPorPaciente
{
    private $repo;

    public function __construct(IngresoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($idPaciente)
    {
        return $this->repo->obtenerAutorizacionesPorPaciente($idPaciente);
    }
}
