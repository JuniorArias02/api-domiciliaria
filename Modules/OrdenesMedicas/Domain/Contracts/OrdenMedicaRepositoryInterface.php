<?php

namespace Modules\OrdenesMedicas\Domain\Contracts;

interface OrdenMedicaRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
}
