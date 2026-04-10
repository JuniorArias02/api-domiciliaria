<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class ObtenerPuntosMapa
{
    private $repo;

    public function __construct(MapaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $filtros)
    {
        // Se pueden añadir validaciones extra aquí.
        return $this->repo->obtenerPuntosPacientes($filtros);
    }
}
