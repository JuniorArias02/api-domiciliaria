<?php

namespace Modules\SabanaClinica\Application\UseCases;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;

class ObtenerFilaSabanaUseCase
{
    private $repository;

    public function __construct(SabanaClinicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $idPaciente)
    {
        return $this->repository->obtenerRegistroPorId($idPaciente);
    }
}
