<?php

namespace Modules\Ingresos\Domain\Contracts;

interface IngresoRepositoryInterface
{
    public function listar();
    public function crear(array $data);
    public function obtenerAutorizacionesPorPaciente($idPaciente);
}
