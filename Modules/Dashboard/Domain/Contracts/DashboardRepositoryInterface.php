<?php

namespace Modules\Dashboard\Domain\Contracts;

interface DashboardRepositoryInterface
{
    public function obtenerKpisOperativos(): array;
    public function obtenerGestionVisitas(): array;
    public function obtenerDemografia(): array;
    public function obtenerGestionRecursos(): array;
    public function obtenerControlCalidad(): array;
    public function obtenerIndicadoresDashboard(): array;
}
