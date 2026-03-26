<?php

namespace Modules\SolicitudesEquipos\Application\UseCases;

use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;

class EliminarSolicitudEquipo
{
    private $repo;

    public function __construct(SolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
