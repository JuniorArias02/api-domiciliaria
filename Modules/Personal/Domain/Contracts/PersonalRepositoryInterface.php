<?php

namespace Modules\Personal\Domain\Contracts;

interface PersonalRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
    public function buscar(string $query, int $limit = 5);
}
