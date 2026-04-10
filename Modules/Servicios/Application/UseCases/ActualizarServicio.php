<?php

namespace Modules\Servicios\Application\UseCases;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Exception;

class ActualizarServicio
{
    private $repo;

    public function __construct(ServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        $servicio = $this->repo->obtenerPorId($id);
        if (!$servicio) {
            throw new Exception("No se puede actualizar un servicio inexistente.");
        }
        return $this->repo->actualizar($id, $data);
    }
}
