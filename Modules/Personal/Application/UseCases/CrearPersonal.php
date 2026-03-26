<?php

namespace Modules\Personal\Application\UseCases;

use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Exception;

class CrearPersonal
{
    private $repo;

    public function __construct(PersonalRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        $camposObligatorios = [
            'nombre_completo',
            'numero_documento',
            'tipo_documento'
        ];

        foreach ($camposObligatorios as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("El campo {$campo} es requerido", 400);
            }
        }

        // id_cargo es un FK obligatorio en base de datos.
        if (empty($data['id_cargo'])) {
            throw new Exception("El campo id_cargo es requerido por la base de datos", 400);
        }

        return $this->repo->crear($data);
    }
}
