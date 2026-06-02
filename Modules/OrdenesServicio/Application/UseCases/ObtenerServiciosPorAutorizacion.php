<?php

namespace Modules\OrdenesServicio\Application\UseCases;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;

class ObtenerServiciosPorAutorizacion
{
    private $repo;

    public function __construct(OrdenServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $autorizacion)
    {
        return $this->repo->obtenerPorAutorizacion($autorizacion);
    }
}
