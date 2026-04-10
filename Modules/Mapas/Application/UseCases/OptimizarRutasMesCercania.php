<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

class OptimizarRutasMesCercania
{
    private $repo;

    public function __construct(MapaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Ejecuta la lógica de optimización de rutas por cercanía geográfica.
     * 
     * @param array $filtros (mes, anio, id_personal)
     * @return array
     */
    public function execute(array $filtros)
    {
        return $this->repo->optimizarRutasMesCercania($filtros);
    }
}
