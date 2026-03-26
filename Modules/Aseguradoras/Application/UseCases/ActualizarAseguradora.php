<?php

namespace Modules\Aseguradoras\Application\UseCases;

use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;

class ActualizarAseguradora
{
    private $repo;

    public function __construct(AseguradoraRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
