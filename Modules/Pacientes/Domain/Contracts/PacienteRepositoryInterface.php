<?php

namespace Modules\Pacientes\Domain\Contracts;

interface PacienteRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function obtenerPaginado(int $porPagina, int $pagina, array $filtros = []);
}
