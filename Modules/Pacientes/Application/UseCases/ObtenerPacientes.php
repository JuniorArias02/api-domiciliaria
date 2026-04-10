<?php

namespace Modules\Pacientes\Application\UseCases;

use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;

class ObtenerPacientes
{
    private PacienteRepositoryInterface $repo;

    public function __construct(PacienteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Obtiene la lista paginada de pacientes con filtros opcionales.
     *
     * @param int    $porPagina  Número de registros por página (default 15, max 100)
     * @param int    $pagina     Número de página actual (default 1)
     * @param array  $filtros    Filtros opcionales: nombre, identificacion, estado, id_aseguradora
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute(int $porPagina = 15, int $pagina = 1, array $filtros = [])
    {
        // Limitar máximo de registros por página para evitar sobrecargas
        $porPagina = min($porPagina, 100);
        $porPagina = max($porPagina, 1);

        return $this->repo->obtenerPaginado($porPagina, $pagina, $filtros);
    }
}
