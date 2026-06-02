<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;

class ListarRutas
{
    private $repo;

    public function __construct(RutaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
