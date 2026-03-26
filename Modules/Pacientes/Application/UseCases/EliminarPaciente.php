<?php

namespace Modules\Pacientes\Application\UseCases;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;

class EliminarPaciente
{
    private $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
