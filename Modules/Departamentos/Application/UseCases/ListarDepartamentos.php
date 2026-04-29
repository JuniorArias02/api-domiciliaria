<?php

namespace Modules\Departamentos\Application\UseCases;

use Modules\Departamentos\Domain\Contracts\DepartamentoRepositoryInterface;

class ListarDepartamentos
{
    private $repo;

    public function __construct(DepartamentoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
