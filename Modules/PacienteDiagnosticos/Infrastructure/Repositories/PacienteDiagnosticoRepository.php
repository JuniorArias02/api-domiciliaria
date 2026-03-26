<?php

namespace Modules\PacienteDiagnosticos\Infrastructure\Repositories;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;
use Modules\PacienteDiagnosticos\Infrastructure\Models\PacienteDiagnostico;
use Exception;

class PacienteDiagnosticoRepository implements PacienteDiagnosticoRepositoryInterface
{
    public function crear(array $data)
    {
        return PacienteDiagnostico::create($data);
    }

    public function actualizar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, array $data)
    {
        $diagnostico = $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico);
        if (!$diagnostico) {
            throw new Exception("Diagnóstico de paciente no encontrado", 404);
        }
        
        // Forma manual de actualizar en llaves compuestas
        PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->update($data);
            
        return $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico);
    }

    public function eliminar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico)
    {
        $diagnostico = $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico);
        if (!$diagnostico) {
            throw new Exception("Diagnóstico de paciente no encontrado", 404);
        }
        
        PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->delete();
            
        return true;
    }

    public function obtener(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico)
    {
        return PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->first();
    }

    public function listar()
    {
        return PacienteDiagnostico::all();
    }
}
