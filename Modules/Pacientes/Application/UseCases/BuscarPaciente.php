<?php

namespace Modules\Pacientes\Application\UseCases;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;

class BuscarPaciente
{
    private $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $query, int $limit = 10)
    {
        if (empty(trim($query))) {
            return [];
        }
        
        return $this->repo->buscar($query, $limit);
    }
}
