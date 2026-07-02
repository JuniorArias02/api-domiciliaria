<?php

namespace Modules\SabanaClinica\Domain\Contracts;

interface SabanaClinicaRepositoryInterface
{
    /**
     * Obtiene los datos consolidados para la Sabana de forma paginada y optimizada.
     */
    public function obtenerGridPaginado(array $filtros, int $perPage);

    /**
     * Obtiene un solo registro completo de la Sabana.
     */
    public function obtenerRegistroPorId(int $idPaciente);

    /**
     * Crea un registro básico.
     */
    public function crearRegistro(array $data);

    /**
     * Actualiza un campo específico (celda) que puede pertenecer a distintas tablas.
     */
    public function actualizarCampo(int $idPaciente, string $campo, $valor);

    /**
     * Elimina un registro de la Sabana (Paciente y relaciones si aplica).
     */
    public function eliminarRegistro(int $idPaciente);
}
