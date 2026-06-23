<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\Rutas\Infrastructure\Exports\RutasExport;

class ExportarRutasExcel
{
    private $repo;

    public function __construct(RutaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        // Obtenemos todas las rutas usando el repositorio
        // Puedes agregar lógica para filtrar por fechas si es necesario
        $rutas = $this->repo->listar();

        // Retornamos el objeto Export que será procesado por el controlador
        return new RutasExport($rutas);
    }
}
