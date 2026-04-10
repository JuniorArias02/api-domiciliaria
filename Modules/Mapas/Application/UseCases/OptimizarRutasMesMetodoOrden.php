<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;

/**
 * Caso de Uso: OptimizarRutasMesMetodoOrden
 * 
 * Este caso de uso se encarga de orientar y optimizar las rutas mensuales de atención
 * basándose estrictamente en el campo 'orden_mapa' definido en la tabla de pacientes.
 * Permite una organización personalizada y fija de los puntos en el mapa.
 */
class OptimizarRutasMesMetodoOrden
{
    private $repository;

    public function __construct(MapaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Ejecuta la lógica de optimización por método de orden manual.
     * 
     * @param array $filtros Filtros opcionales (comuna, especialidad, personal, etc.)
     * @return array Lista de pacientes ordenados geométricamente por orden_mapa.
     */
    public function execute(array $filtros)
    {
        return $this->repository->optimizarRutasMesMetodoOrden($filtros);
    }
}
