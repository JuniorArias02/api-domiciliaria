<?php

namespace Modules\Laboratorios\Application\UseCases;

use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;
use Exception;

class CrearLaboratorio
{
    private $repo;

    public function __construct(LaboratorioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }
        if (empty($data['fecha_solicitud'])) {
            throw new Exception("El campo fecha_solicitud es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
