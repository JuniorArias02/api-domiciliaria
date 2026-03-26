<?php

namespace Modules\Barrios\Application\UseCases;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;

class EliminarBarrio
{
    private $repo;

    public function __construct(BarrioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
