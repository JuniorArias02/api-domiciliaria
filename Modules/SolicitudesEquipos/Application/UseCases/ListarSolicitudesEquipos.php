<?php

namespace Modules\SolicitudesEquipos\Application\UseCases;

use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;

class ListarSolicitudesEquipos
{
    private $repo;

    public function __construct(SolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
