<?php

namespace Modules\RegistroPrograma\Application\UseCases;

use Modules\RegistroPrograma\Domain\Contracts\RegistroProgramaRepositoryInterface;

class ObtenerAutorizacionesPorPaciente
{
    private $repo;

    public function __construct(RegistroProgramaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $idPaciente)
    {
        return $this->repo->obtenerAutorizacionesPorPaciente($idPaciente);
    }
}
