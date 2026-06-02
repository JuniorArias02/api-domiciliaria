<?php

namespace Modules\Rutas\Domain\Contracts;

interface RutaRepositoryInterface
{
    public function crear(array $data);
    public function actualizar(int $id, array $data);
    public function eliminar(int $id);
    public function obtenerPorId(int $id);
    public function listar();
    public function existeRutaParaPersonalEnFecha(int $idPersonal, string $fecha): bool;
}
