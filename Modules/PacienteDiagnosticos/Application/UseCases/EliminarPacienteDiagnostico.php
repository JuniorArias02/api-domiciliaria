<?php

namespace Modules\PacienteDiagnosticos\Application\UseCases;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;

class EliminarPacienteDiagnostico
{
    private $repo;

    public function __construct(PacienteDiagnosticoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, int $id_visita)
    {
        return $this->repo->eliminar($id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita);
    }
}
