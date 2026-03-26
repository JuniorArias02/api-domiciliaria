<?php

namespace Modules\Cuidadores\Application\UseCases;

use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;

class ListarCuidadores
{
    private $repo;

    public function __construct(CuidadorRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
