<?php

namespace Modules\Comunas\Application\UseCases;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;

class ActualizarComuna
{
    private $repo;

    public function __construct(ComunaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
