<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Exception;

class ObtenerRuta
{
    private $repo;

    public function __construct(RutaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        $ruta = $this->repo->obtenerPorId($id);
        if (!$ruta) {
            throw new Exception("Ruta no encontrada", 404);
        }
        return $ruta;
    }
}
