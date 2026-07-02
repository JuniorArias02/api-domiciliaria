<?php

namespace Modules\SabanaClinica\Application\UseCases;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;

class EliminarFilaSabanaUseCase
{
    private $repository;

    public function __construct(SabanaClinicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $idPaciente)
    {
        return $this->repository->eliminarRegistro($idPaciente);
    }
}
