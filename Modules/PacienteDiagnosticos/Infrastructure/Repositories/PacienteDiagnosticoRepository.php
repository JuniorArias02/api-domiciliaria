<?php

namespace Modules\PacienteDiagnosticos\Infrastructure\Repositories;

use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;
use Modules\PacienteDiagnosticos\Infrastructure\Models\PacienteDiagnostico;
use Exception;

class PacienteDiagnosticoRepository implements PacienteDiagnosticoRepositoryInterface
{
    /**
     * Crea un nuevo diagnóstico para un paciente vinculado a una visita.
     */
    public function crear(array $data)
    {
        return PacienteDiagnostico::create($data);
    }

    /**
     * Actualiza un diagnóstico específico identificado por paciente, CIE10, tipo y visita.
     */
    public function actualizar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, int $id_visita, array $data)
    {
        $diagnostico = $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita);
        if (!$diagnostico) {
            throw new Exception("Diagnóstico de paciente no encontrado para la visita proporcionada", 404);
        }
        
        PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->where('id_visita', $id_visita)
            ->update($data);
            
        return $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita);
    }

    /**
     * Elimina un diagnóstico específico.
     */
    public function eliminar(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, int $id_visita)
    {
        $diagnostico = $this->obtener($id_paciente, $codigo_cie10, $tipo_diagnostico, $id_visita);
        if (!$diagnostico) {
            throw new Exception("Diagnóstico de paciente no encontrado", 404);
        }
        
        PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->where('id_visita', $id_visita)
            ->delete();
            
        return true;
    }

    /**
     * Obtiene un diagnóstico único filtrando por todos los campos de la PK compuesta.
     */
    public function obtener(int $id_paciente, string $codigo_cie10, string $tipo_diagnostico, int $id_visita)
    {
        return PacienteDiagnostico::where('id_paciente', $id_paciente)
            ->where('codigo_cie10', $codigo_cie10)
            ->where('tipo_diagnostico', $tipo_diagnostico)
            ->where('id_visita', $id_visita)
            ->first();
    }

    public function listar()
    {
        return PacienteDiagnostico::all();
    }
}
