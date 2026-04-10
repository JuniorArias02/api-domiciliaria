<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class ObtenerPacientesPorComuna
{
    private $repo;

    public function __construct(MapaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Ejecuta el caso de uso para obtener pacientes por comuna.
     * 
     * @param int $id_comuna
     * @return array
     */
    public function execute(int $id_comuna)
    {
        return $this->repo->obtenerPacientesPorComuna($id_comuna);
    }
}
