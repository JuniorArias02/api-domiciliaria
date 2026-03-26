<?php

namespace Modules\Telexperticias\Application\UseCases;

use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;
use Exception;

class CrearTelexperticia
{
    private $repo;

    public function __construct(TelexperticiaRepositoryInterface $repo)
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
        if (empty($data['fecha_solicitud'])) {
            throw new Exception("El campo fecha_solicitud es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
