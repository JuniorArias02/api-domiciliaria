<?php

namespace Modules\PacienteDiagnosticos\Application\UseCases;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;
use Exception;

class CrearPacienteDiagnostico
{
    private $repo;

    public function __construct(PacienteDiagnosticoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido", 400);
        }
        if (empty($data['codigo_cie10'])) {
            throw new Exception("El campo codigo_cie10 es requerido", 400);
        }

        return $this->repo->crear($data);
    }
}
