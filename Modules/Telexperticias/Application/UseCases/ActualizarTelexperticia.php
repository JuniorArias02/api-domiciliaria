<?php

namespace Modules\Telexperticias\Application\UseCases;

use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;

class ActualizarTelexperticia
{
    private $repo;

    public function __construct(TelexperticiaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        return $this->repo->actualizar($id, $data);
    }
}
