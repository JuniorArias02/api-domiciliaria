<?php

namespace Modules\Tutelas\Application\UseCases;

use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;

class ActualizarTutela
{
    private $repo;

    public function __construct(TutelaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
