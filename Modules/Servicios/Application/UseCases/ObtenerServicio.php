<?php

namespace Modules\Servicios\Application\UseCases;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Exception;

class ObtenerServicio
{
    private $repo;

    public function __construct(ServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        $servicio = $this->repo->obtenerPorId($id);
        if (!$servicio) {
            throw new Exception("El servicio solicitado no existe.");
        }
        return $servicio;
    }
}
