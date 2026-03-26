<?php

namespace Modules\Laboratorios\Application\UseCases;

use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;

class EliminarLaboratorio
{
    private $repo;

    public function __construct(LaboratorioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
