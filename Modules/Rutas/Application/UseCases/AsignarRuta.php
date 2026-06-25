<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Exception;

class AsignarRuta
{
    private $repo;

    public function __construct(RutaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $idRuta)
    {
        $ruta = $this->repo->obtenerPorId($idRuta);
        if (!$ruta) {
            throw new Exception("La ruta especificada no existe", 404);
        }

        if ($ruta->estado !== 'EN_DISENO') {
            throw new Exception("La ruta solo puede ser asignada si se encuentra en estado EN_DISENO", 400);
        }

        // Se podrían añadir validaciones extra, por ejemplo, que tenga al menos una visita
        if ($ruta->visitas->isEmpty()) {
            throw new Exception("No se puede asignar una ruta que no tiene visitas asociadas", 400);
        }

        return $this->repo->actualizar($idRuta, [
            'estado' => 'ASIGNADA'
        ]);
    }
}
