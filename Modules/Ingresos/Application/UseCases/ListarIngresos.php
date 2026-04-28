<?php

namespace Modules\Ingresos\Application\UseCases;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;

class ListarIngresos
{
    private $repo;

    public function __construct(IngresoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
