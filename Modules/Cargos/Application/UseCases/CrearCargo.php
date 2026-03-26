<?php

namespace Modules\Cargos\Application\UseCases;

use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;
use Exception;

class CrearCargo
{
    private $repo;

    public function __construct(CargosRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre'])) {
            throw new Exception("El campo nombre es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
