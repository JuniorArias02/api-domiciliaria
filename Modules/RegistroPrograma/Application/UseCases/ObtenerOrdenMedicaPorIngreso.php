<?php

namespace Modules\RegistroPrograma\Application\UseCases;

use Modules\RegistroPrograma\Domain\Contracts\RegistroProgramaRepositoryInterface;

class ObtenerOrdenMedicaPorIngreso
{
    private $repo;

    public function __construct(RegistroProgramaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($ingreso)
    {
        return $this->repo->obtenerOrdenMedicaPorIngreso($ingreso);
    }
}
