<?php

namespace Modules\Gateway\Application\UseCases;

use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Exception;

class ConsultarDetalleIngreso
{
    private $repo;

    public function __construct(GatewayRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($idIngreso)
    {
        if (empty($idIngreso) || !is_numeric($idIngreso)) {
            throw new Exception("El ID del ingreso proporcionado no es válido.");
        }

        return $this->repo->consultarDetalleIngreso($idIngreso);
    }
}
