<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class ObtenerOrdenesPaciente
{
    private $repository;

    public function __construct(MapaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $idPaciente)
    {
        if ($idPaciente <= 0) {
            throw new \InvalidArgumentException("El ID del paciente no es válido.");
        }

        $resultado = $this->repository->obtenerOrdenesPaciente($idPaciente);

        if (!$resultado) {
            throw new \Exception("Paciente no encontrado.", 404);
        }

        return $resultado;
    }
}
