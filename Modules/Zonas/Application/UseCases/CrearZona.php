<?php

namespace Modules\Zonas\Application\UseCases;

use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;
use Exception;

class CrearZona
{
    private $repo;

    public function __construct(ZonaRepositoryInterface $repo)
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
