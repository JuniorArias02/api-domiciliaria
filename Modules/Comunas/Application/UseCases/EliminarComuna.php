<?php

namespace Modules\Comunas\Application\UseCases;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;

class EliminarComuna
{
    private $repo;

    public function __construct(ComunaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
