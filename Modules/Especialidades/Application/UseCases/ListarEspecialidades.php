<?php

namespace Modules\Especialidades\Application\UseCases;

use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;

class ListarEspecialidades
{
    private $repo;

    public function __construct(EspecialidadRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
