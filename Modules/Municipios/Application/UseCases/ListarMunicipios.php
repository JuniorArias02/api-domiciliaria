<?php

namespace Modules\Municipios\Application\UseCases;

use Modules\Municipios\Domain\Contracts\MunicipioRepositoryInterface;

class ListarMunicipios
{
    private $repo;

    public function __construct(MunicipioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
