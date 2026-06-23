<?php

namespace Modules\Personal\Application\UseCases;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Exception;

class ObtenerIngresosInvolucradosUseCase
{
    private $repository;

    public function __construct(PersonalRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id)
    {
        // Validar si el personal existe
        $personal = $this->repository->obtenerPorId($id);
        if (!$personal) {
            throw new Exception("Personal no encontrado", 404);
        }

        return $this->repository->obtenerIngresosInvolucrados($id);
    }
}
