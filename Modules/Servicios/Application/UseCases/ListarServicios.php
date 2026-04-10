<?php

namespace Modules\Servicios\Application\UseCases;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;

class ListarServicios
{
    private $repo;

    public function __construct(ServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
