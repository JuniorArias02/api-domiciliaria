<?php

namespace Modules\Barrios\Domain\Contracts;

interface BarrioRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
}
