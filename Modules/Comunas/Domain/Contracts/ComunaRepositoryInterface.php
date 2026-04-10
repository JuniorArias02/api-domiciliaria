<?php

namespace Modules\Comunas\Domain\Contracts;

interface ComunaRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function obtenerPorZona(int $id_zona);
    public function listar();
}
