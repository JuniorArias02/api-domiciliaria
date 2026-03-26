<?php

namespace Modules\Barrios\Application\UseCases;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;

class ListarBarrios
{
    private $repo;

    public function __construct(BarrioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
