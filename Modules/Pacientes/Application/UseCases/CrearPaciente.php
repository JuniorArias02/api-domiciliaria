<?php

namespace Modules\Pacientes\Application\UseCases;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;
use Exception;

class CrearPaciente
{
    private $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        $camposObligatorios = [
            'nombre_completo',
            'identificacion',
            'sexo',
            'tipo_documento',
            'fecha_ingreso',
        ];

        foreach ($camposObligatorios as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("El campo {$campo} es requerido", 400);
            }
        }

        // Otros campos requeridos por la DB
        $camposObligatoriosDB = [
            'fecha_nacimiento',
            'id_aseguradora',
            'direccion'
        ];
        
        foreach ($camposObligatoriosDB as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("El campo {$campo} es requerido por la base de datos", 400);
            }
        }

        return $this->repo->crear($data);
    }
}
