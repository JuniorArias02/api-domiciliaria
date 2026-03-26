<?php

namespace Modules\Cuidadores\Application\UseCases;

use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;

class ActualizarCuidador
{
    private $repo;

    public function __construct(CuidadorRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
