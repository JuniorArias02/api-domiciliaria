<?php

namespace Modules\Tutelas\Application\UseCases;

use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;

class EliminarTutela
{
    private $repo;

    public function __construct(TutelaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
