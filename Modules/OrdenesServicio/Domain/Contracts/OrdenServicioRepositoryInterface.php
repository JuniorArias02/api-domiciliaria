<?php

namespace Modules\OrdenesServicio\Domain\Contracts;

interface OrdenServicioRepositoryInterface
{
    public function listar();
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function obtenerPorId(int $id);
    public function obtenerPorAutorizacion(string $autorizacion);
    public function obtenerHistorialPorPaciente(int $idPaciente, ?int $idServicio = null);
    public function buscarContinuidades(int $idPaciente, array $filtros);
}

