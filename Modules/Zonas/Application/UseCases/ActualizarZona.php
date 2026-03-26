<?php

namespace Modules\Zonas\Application\UseCases;

use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;

class ActualizarZona
{
    private $repo;

    public function __construct(ZonaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
