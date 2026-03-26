<?php

namespace Modules\Cargos\Application\UseCases;

use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;

class ListarCargos
{
    private $repo;

    public function __construct(CargosRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
