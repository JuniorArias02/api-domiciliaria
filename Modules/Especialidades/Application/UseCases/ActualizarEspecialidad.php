<?php

namespace Modules\Especialidades\Application\UseCases;

use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;

class ActualizarEspecialidad
{
    private $repo;

    public function __construct(EspecialidadRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
