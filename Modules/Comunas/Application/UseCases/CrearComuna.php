<?php

namespace Modules\Comunas\Application\UseCases;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;
use Exception;

class CrearComuna
{
    private $repo;

    public function __construct(ComunaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['nombre'])) {
            throw new Exception("El campo nombre es requerido", 400);
        }
        if (empty($data['id_zona'])) {
            throw new Exception("El campo id_zona es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
