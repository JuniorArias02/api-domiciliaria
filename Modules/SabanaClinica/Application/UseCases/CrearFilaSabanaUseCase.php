<?php

namespace Modules\SabanaClinica\Application\UseCases;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;

class CrearFilaSabanaUseCase
{
    private $repository;

    public function __construct(SabanaClinicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data)
    {
        return $this->repository->crearRegistro($data);
    }
}
