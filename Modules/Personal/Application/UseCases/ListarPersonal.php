<?php

namespace Modules\Personal\Application\UseCases;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;

class ListarPersonal
{
    private $repo;

    public function __construct(PersonalRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
