<?php

namespace Modules\Cargos\Application\UseCases;

use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;

class ActualizarCargo
{
    private $repo;

    public function __construct(CargosRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
