<?php

namespace Modules\SolicitudesEquipos\Application\UseCases;

use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;
use Exception;

class CrearSolicitudEquipo
{
    private $repo;

    public function __construct(SolicitudEquipoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
