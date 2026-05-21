<?php

namespace Modules\RegistroPrograma\Domain\Contracts;

interface RegistroProgramaRepositoryInterface
{
    public function obtenerPacientes(int $porPagina = 30, int $pagina = 1, array $filtros = []);

    public function obtenerAutorizacionesPorPaciente(int $idPaciente);

    public function obtenerOrdenMedicaPorIngreso($ingreso);
}
