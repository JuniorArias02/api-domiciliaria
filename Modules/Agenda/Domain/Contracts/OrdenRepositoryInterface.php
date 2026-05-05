<?php

declare(strict_types=1);

namespace Modules\Agenda\Domain\Contracts;

interface OrdenRepositoryInterface
{
    /**
     * @param array $datosOrden
     * @return int Retorna el ID de la recién creada orden médica
     */
    public function crearOrden(array $datosOrden): int;
    /**
     * @param array $filtros
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listarPaginado(array $filtros, int $perPage): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * @param array $filtros
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listarAgendasDetalladas(array $filtros, int $perPage): \Illuminate\Pagination\LengthAwarePaginator;
}
