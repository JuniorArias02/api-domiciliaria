<?php

namespace Modules\DetalleSolicitudEquipos\Application\UseCases;

use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;

class ActualizarDetalleSolicitudEquipo
{
    private $repo;

    public function __construct(DetalleSolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
