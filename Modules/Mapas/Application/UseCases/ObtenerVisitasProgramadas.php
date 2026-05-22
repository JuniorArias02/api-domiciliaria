<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;

class ObtenerVisitasProgramadas
{
    private $repo;
    private $servicioRepo;

    public function __construct(
        MapaRepositoryInterface $repo,
        ServicioRepositoryInterface $servicioRepo
    ) {
        $this->repo = $repo;
        $this->servicioRepo = $servicioRepo;
    }

    /**
     * Ejecuta el caso de uso para obtener todas las visitas en estado PROGRAMADA.
     * 
     * @param array $params ['mes' => int, 'anio' => int, 'id_servicio' => int, 'id_profesional' => int]
     * @return array
     */
    public function execute(array $params): array
    {
        if (empty($params['mes'])) {
            throw new \InvalidArgumentException("El parámetro 'mes' es obligatorio.");
        }

        if (!empty($params['id_servicio'])) {
            $servicio = $this->servicioRepo->obtenerPorId((int) $params['id_servicio']);
            if (!$servicio) {
                throw new \InvalidArgumentException("El servicio seleccionado no existe o no es válido.");
            }
        }

        return $this->repo->obtenerVisitasProgramadas($params);
    }
}
