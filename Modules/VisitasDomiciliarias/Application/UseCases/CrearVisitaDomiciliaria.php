<?php

namespace Modules\VisitasDomiciliarias\Application\UseCases;

use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Exception;

class CrearVisitaDomiciliaria
{
    private $repo;

    public function __construct(VisitaDomiciliariaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }
        if (empty($data['id_personal'])) {
            throw new Exception("El campo id_personal es requerido por la base de datos", 400);
        }
        if (empty($data['id_orden_servicio'])) {
            throw new Exception("El campo id_orden_servicio es requerido por la base de datos", 400);
        }
        if (empty($data['fecha_programada'])) {
            throw new Exception("El campo fecha_programada es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
