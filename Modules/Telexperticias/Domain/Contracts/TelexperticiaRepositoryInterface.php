<?php

namespace Modules\Telexperticias\Domain\Contracts;

interface TelexperticiaRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
}
