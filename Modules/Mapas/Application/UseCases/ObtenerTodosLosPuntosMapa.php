<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class ObtenerTodosLosPuntosMapa
{
    private $repo;

    public function __construct(MapaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $filtros)
    {
        return $this->repo->obtenerTodosLosPuntos($filtros);
    }
}
