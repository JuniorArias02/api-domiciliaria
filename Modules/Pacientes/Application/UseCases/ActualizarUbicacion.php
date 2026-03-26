<?php

namespace Modules\Pacientes\Application\UseCases;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;
use Exception;

class ActualizarUbicacion
{
    private $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id, array $data)
    {
        if (empty($data['latitud']) || empty($data['longitud']) || empty($data['url_google_maps'])) {
            throw new Exception("latitud, longitud y url_google_maps son obligatorios para actualizar la ubicación", 400);
        }

        $ubicacionData = [
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
            'url_google_maps' => $data['url_google_maps']
        ];

        return $this->repo->actualizar($id, $ubicacionData);
    }
}
