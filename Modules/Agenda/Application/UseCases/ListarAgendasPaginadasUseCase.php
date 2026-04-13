<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\UseCases;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Agenda\Application\Contracts\ListarAgendasPaginadasUseCaseInterface;
use Modules\Agenda\Application\DTO\PaginacionAgendaInputDTO;
use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;

class ListarAgendasPaginadasUseCase implements ListarAgendasPaginadasUseCaseInterface
{
    public function __construct(
        private readonly OrdenRepositoryInterface $ordenRepository
    ) {
    }

    public function execute(PaginacionAgendaInputDTO $input): LengthAwarePaginator
    {
        $filtros = [
            'buscar' => $input->buscar,
            'estado' => $input->estado,
        ];

        return $this->ordenRepository->listarPaginado($filtros, $input->per_page);
    }
}
