<?php

namespace Modules\Ingresos\Application\UseCases;

use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Exception;

class CrearIngreso
{
    private $repo;

    public function __construct(IngresoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        // Validaciones básicas
        if (empty($data['id_paciente'])) {
            throw new Exception("El ID del paciente es requerido");
        }

        if (empty($data['ingreso'])) {
            throw new Exception("El valor del ingreso es requerido");
        }

        if (empty($data['fecha_ingreso'])) {
            throw new Exception("La fecha de ingreso es requerida");
        }

        return $this->repo->crear($data);
    }
}
