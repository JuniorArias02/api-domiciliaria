<?php

namespace Modules\Comunas\Application\UseCases;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;

class ListarComunas
{
    private $repo;

    public function __construct(ComunaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
