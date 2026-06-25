<?php

namespace Modules\OrdenesServicio\Application\UseCases;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Exception;

class ObtenerHistorialServiciosPaciente
{
    private $repo;

    public function __construct(OrdenServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $idPaciente, ?int $idServicio = null)
    {
        if (empty($idPaciente) || $idPaciente <= 0) {
            throw new Exception("El ID del paciente no es válido");
        }

        return $this->repo->obtenerHistorialPorPaciente($idPaciente, $idServicio);
    }
}
