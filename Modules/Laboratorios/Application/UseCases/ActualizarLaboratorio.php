<?php

namespace Modules\Laboratorios\Application\UseCases;

use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;

class ActualizarLaboratorio
{
    private $repo;

    public function __construct(LaboratorioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
