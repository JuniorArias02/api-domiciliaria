<?php

namespace Modules\SabanaClinica\Application\UseCases;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;

class ActualizarColumnaSabanaUseCase
{
    private $repository;

    public function __construct(SabanaClinicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $idPaciente, array $data)
    {
        // Se espera un request con: field (nombre de la columna) y value (nuevo valor)
        $campo = $data['field'] ?? null;
        $valor = $data['value'] ?? null;

        if (!$campo) {
            throw new \Exception("Debe proveer el 'field' a actualizar.");
        }

        return $this->repository->actualizarCampo($idPaciente, $campo, $valor);
    }
}
