<?php

namespace Modules\Barrios\Application\UseCases;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;
use Exception;

class CrearBarrio
{
    private $repo;

    public function __construct(BarrioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre'])) {
            throw new Exception("El campo nombre es requerido", 400);
        }
        if (empty($data['id_comuna'])) {
            throw new Exception("El campo id_comuna es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
