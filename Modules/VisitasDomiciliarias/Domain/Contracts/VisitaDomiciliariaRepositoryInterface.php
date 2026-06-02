<?php

namespace Modules\VisitasDomiciliarias\Domain\Contracts;

interface VisitaDomiciliariaRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
    public function existeVisitaAsignadaAPacienteEnFecha(int $idPaciente, string $fecha): bool;
    public function contarVisitasActivasPorOrdenServicio(int $idOrdenServicio): int;
}
