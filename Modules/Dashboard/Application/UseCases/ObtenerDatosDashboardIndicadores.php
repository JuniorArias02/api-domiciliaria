<?php

namespace Modules\Dashboard\Application\UseCases;

use Modules\Dashboard\Domain\Contracts\DashboardRepositoryInterface;

class ObtenerDatosDashboardIndicadores
{
    private DashboardRepositoryInterface $repo;

    public function __construct(DashboardRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(): array
    {
        return $this->repo->obtenerIndicadoresDashboard();
    }
}
