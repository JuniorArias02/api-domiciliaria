<?php

namespace Modules\Tutelas\Application\UseCases;

use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;
use Exception;

class CrearTutela
{
    private $repo;

    public function __construct(TutelaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        if (empty($data['numero_tutela'])) {
            throw new Exception("El campo numero_tutela es requerido", 400);
        }
        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
