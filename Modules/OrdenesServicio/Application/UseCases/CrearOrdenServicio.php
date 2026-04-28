<?php

namespace Modules\OrdenesServicio\Application\UseCases;

use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Exception;

class CrearOrdenServicio
{
    private $repo;

    public function __construct(OrdenServicioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_orden'])) {
            throw new Exception("El ID de la orden médica es requerido");
        }

        if (empty($data['id_servicio'])) {
            throw new Exception("El ID del servicio es requerido");
        }

        if (empty($data['numero_sesiones'])) {
            throw new Exception("El número de sesiones es requerido");
        }

        // Estado por defecto si no se proporciona
        $data['estado'] = $data['estado'] ?? 'PROGRAMADA';

        return $this->repo->crear($data);
    }
}
