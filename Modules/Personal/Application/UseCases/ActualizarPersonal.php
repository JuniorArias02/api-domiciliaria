<?php

namespace Modules\Personal\Application\UseCases;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;

class ActualizarPersonal
{
    private $repo;

    public function __construct(PersonalRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
