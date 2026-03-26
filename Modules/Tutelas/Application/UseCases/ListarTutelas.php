<?php

namespace Modules\Tutelas\Application\UseCases;

use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;

class ListarTutelas
{
    private $repo;

    public function __construct(TutelaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
