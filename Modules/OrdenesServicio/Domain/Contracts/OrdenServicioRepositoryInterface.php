<?php

namespace Modules\OrdenesServicio\Domain\Contracts;

interface OrdenServicioRepositoryInterface
{
    public function listar();
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function obtenerPorId(int $id);
}

