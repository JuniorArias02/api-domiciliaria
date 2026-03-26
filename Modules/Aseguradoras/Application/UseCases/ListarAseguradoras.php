<?php

namespace Modules\Aseguradoras\Application\UseCases;

use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;

class ListarAseguradoras
{
    private $repo;

    public function __construct(AseguradoraRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
