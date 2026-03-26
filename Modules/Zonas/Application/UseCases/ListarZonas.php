<?php

namespace Modules\Zonas\Application\UseCases;

use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;

class ListarZonas
{
    private $repo;

    public function __construct(ZonaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
