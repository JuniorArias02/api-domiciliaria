<?php

namespace Modules\Cuidadores\Application\UseCases;

use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;
use Exception;

class CrearCuidador
{
    private $repo;

    public function __construct(CuidadorRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre_completo'])) {
            throw new Exception("El campo nombre_completo es requerido", 400);
        }
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
