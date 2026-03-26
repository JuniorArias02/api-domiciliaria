<?php

namespace Modules\DetalleSolicitudEquipos\Application\UseCases;

use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;

class EliminarDetalleSolicitudEquipo
{
    private $repo;

    public function __construct(DetalleSolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
