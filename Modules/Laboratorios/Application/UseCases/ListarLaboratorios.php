<?php

namespace Modules\Laboratorios\Application\UseCases;

use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;

class ListarLaboratorios
{
    private $repo;

    public function __construct(LaboratorioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
