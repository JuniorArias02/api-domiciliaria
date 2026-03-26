<?php

namespace Modules\Especialidades\Application\UseCases;

use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;
use Exception;

class CrearEspecialidad
{
    private $repo;

    public function __construct(EspecialidadRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre'])) {
            throw new Exception("El campo nombre es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
