<?php

namespace Modules\OrdenesServicio\Application\UseCases;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;

class ListarOrdenesServicio
{
    private $repo;

    public function __construct(OrdenServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
