<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Exception;

class ObtenerDetallePunto
{
    private $repo;

    public function __construct(MapaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id_paciente)
    {
        $detalle = $this->repo->obtenerDetallePaciente($id_paciente);
        
        if (!$detalle) {
            throw new Exception("Paciente no encontrado", 404);
        }

        return $detalle;
    }
}
