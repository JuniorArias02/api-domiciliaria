<?php

namespace Modules\Especialidades\Application\UseCases;

use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;

class EliminarEspecialidad
{
    private $repo;

    public function __construct(EspecialidadRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
