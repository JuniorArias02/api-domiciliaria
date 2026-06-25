<?php

namespace Modules\OrdenesServicio\Application\UseCases;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Exception;

class BuscarContinuidadesAvanzado
{
    private $repo;

    public function __construct(OrdenServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $idPaciente, array $filtros)
    {
        if (empty($idPaciente) || $idPaciente <= 0) {
            throw new Exception("El ID del paciente no es válido");
        }

        return $this->repo->buscarContinuidades($idPaciente, $filtros);
    }
}
