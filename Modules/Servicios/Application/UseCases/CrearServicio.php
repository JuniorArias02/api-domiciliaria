<?php

namespace Modules\Servicios\Application\UseCases;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Exception;

class CrearServicio
{
    private $repo;

    public function __construct(ServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['codigo_servicio'])) {
            throw new Exception("El código del servicio es obligatorio.");
        }
        if (empty($data['nombre_servicio'])) {
            throw new Exception("El nombre del servicio es obligatorio.");
        }
        return $this->repo->crear($data);
    }
}
