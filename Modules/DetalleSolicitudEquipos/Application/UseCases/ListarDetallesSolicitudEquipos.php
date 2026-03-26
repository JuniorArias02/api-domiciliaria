<?php

namespace Modules\DetalleSolicitudEquipos\Application\UseCases;

use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;

class ListarDetallesSolicitudEquipos
{
    private $repo;

    public function __construct(DetalleSolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
