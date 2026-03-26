<?php

namespace Modules\Cuidadores\Application\UseCases;

use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;

class EliminarCuidador
{
    private $repo;

    public function __construct(CuidadorRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
