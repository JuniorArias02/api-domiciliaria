<?php

namespace Modules\Aseguradoras\Application\UseCases;

use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;

class EliminarAseguradora
{
    private $repo;

    public function __construct(AseguradoraRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
