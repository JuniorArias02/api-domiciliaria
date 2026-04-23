<?php

namespace Modules\Dashboard\Application\UseCases;

use Modules\Dashboard\Domain\Contracts\DashboardRepositoryInterface;

class ObtenerDatosDashboard
{
    private DashboardRepositoryInterface $repo;

    public function __construct(DashboardRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(): array
    {
        return [
            'kpis_operativos' => $this->repo->obtenerKpisOperativos(),
            'gestion_visitas' => $this->repo->obtenerGestionVisitas(),
            'demografia' => $this->repo->obtenerDemografia(),
            // 'gestion_recursos' => $this->repo->obtenerGestionRecursos(),
            // 'control_calidad' => $this->repo->obtenerControlCalidad(),
        ];
    }
}
