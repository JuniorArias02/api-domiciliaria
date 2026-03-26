<?php

namespace Modules\Aseguradoras\Application\UseCases;

use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Exception;

class CrearAseguradora
{
    private $repo;

    public function __construct(AseguradoraRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre'])) {
            throw new Exception("El campo nombre es obligatorio", 400);
        }
        
        return $this->repo->crear($data);
    }
}
