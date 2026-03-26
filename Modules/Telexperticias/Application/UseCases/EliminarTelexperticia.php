<?php

namespace Modules\Telexperticias\Application\UseCases;

use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;

class EliminarTelexperticia
{
    private $repo;

    public function __construct(TelexperticiaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->eliminar($id);
    }
}
