<?php

namespace Modules\Zonas\Application\UseCases;

use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;

class EliminarZona
{
    private $repo;

    public function __construct(ZonaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
