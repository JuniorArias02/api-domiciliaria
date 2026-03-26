<?php

namespace Modules\PacienteDiagnosticos\Application\UseCases;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;

class ListarPacienteDiagnosticos
{
    private $repo;

    public function __construct(PacienteDiagnosticoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
