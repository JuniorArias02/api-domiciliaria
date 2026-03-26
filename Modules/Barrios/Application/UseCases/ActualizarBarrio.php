<?php

namespace Modules\Barrios\Application\UseCases;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;

class ActualizarBarrio
{
    private $repo;

    public function __construct(BarrioRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
