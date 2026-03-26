<?php

namespace Modules\PacienteDiagnosticos\Domain\Contracts;

interface PacienteDiagnosticoRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, array $data);
    public function eliminar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico);
    public function obtener(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico);
    public function listar();
}
