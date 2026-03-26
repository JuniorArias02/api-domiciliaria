<?php

namespace Modules\OrdenesMedicas\Application\UseCases;

use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Exception;

class CrearOrdenMedica
{
    private $repo;

    public function __construct(OrdenMedicaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }
        if (empty($data['id_especialidad'])) {
            throw new Exception("El campo id_especialidad es requerido por la base de datos", 400);
        }
        if (empty($data['fecha_orden'])) {
            throw new Exception("El campo fecha_orden es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
