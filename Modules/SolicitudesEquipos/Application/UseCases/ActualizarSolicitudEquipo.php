<?php

namespace Modules\SolicitudesEquipos\Application\UseCases;

use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;

class ActualizarSolicitudEquipo
{
    private $repo;

    public function __construct(SolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
