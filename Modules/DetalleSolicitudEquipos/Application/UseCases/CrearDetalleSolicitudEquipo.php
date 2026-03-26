<?php

namespace Modules\DetalleSolicitudEquipos\Application\UseCases;

use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;
use Exception;

class CrearDetalleSolicitudEquipo
{
    private $repo;

    public function __construct(DetalleSolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_solicitud'])) {
            throw new Exception("El campo id_solicitud es requerido por la base de datos", 400);
        }
        if (empty($data['id_equipo'])) {
            throw new Exception("El campo id_equipo es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
