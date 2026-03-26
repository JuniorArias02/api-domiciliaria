<?php

namespace Modules\OrdenesMedicas\Application\UseCases;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;

class EliminarOrdenMedica
{
    private $repo;

    public function __construct(OrdenMedicaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
