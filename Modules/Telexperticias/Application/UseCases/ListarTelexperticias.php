<?php

namespace Modules\Telexperticias\Application\UseCases;

use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;

class ListarTelexperticias
{
    private $repo;

    public function __construct(TelexperticiaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->listar();
    }
}
