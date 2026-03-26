<?php

namespace Modules\DetalleSolicitudEquipos\Domain\Contracts;

interface DetalleSolicitudEquipoRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
}
