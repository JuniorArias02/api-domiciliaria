<?php

namespace Modules\RegistroPrograma\Application\UseCases;

use Modules\RegistroPrograma\Domain\Contracts\RegistroProgramaRepositoryInterface;

class ObtenerPacientesRegistroPrograma
{
    private $repo;

    public function __construct(RegistroProgramaRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $porPagina = 30, int $pagina = 1, array $filtros = [])
    {
        return $this->repo->obtenerPacientes($porPagina, $pagina, $filtros);
    }
}
