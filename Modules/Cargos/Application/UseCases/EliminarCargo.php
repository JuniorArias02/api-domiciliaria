<?php

namespace Modules\Cargos\Application\UseCases;

use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;

class EliminarCargo
{
    private $repo;

    public function __construct(CargosRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
