<?php

namespace Modules\OrdenesMedicas\Application\UseCases;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;

class ListarOrdenesMedicas
{
    private $repo;

    public function __construct(OrdenMedicaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
