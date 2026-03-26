<?php

namespace Modules\VisitasDomiciliarias\Application\UseCases;

use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;

class ListarVisitasDomiciliarias
{
    private $repo;

    public function __construct(VisitaDomiciliariaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
