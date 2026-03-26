<?php

namespace Modules\PacienteDiagnosticos\Application\UseCases;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;

class ActualizarPacienteDiagnostico
{
    private $repo;

    public function __construct(PacienteDiagnosticoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, array $data)
    {
        return $this->repo->actualizar($id_paciente, $codigo_cie10, $tipo_diagnostico, $data);
    }
}
