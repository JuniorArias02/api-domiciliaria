<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\UseCases;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Agenda\Application\Contracts\ListarAgendasDetalladasUseCaseInterface;
use Modules\Agenda\Application\DTO\PaginacionAgendaInputDTO;
use Modules\Agenda\Domain\Contracts\OrdenRepositoryInterface;

class ListarAgendasDetalladasUseCase implements ListarAgendasDetalladasUseCaseInterface
{
    public function __construct(
        private readonly OrdenRepositoryInterface $ordenRepository
    ) {}

    public function execute(PaginacionAgendaInputDTO $input): LengthAwarePaginator
    {
        // El usuario puede poner el límite que guste entre 10 y 150
        $perPage = $input->per_page;
        
        if ($perPage < 10) {
            $perPage = 10;
        }
        
        if ($perPage > 150) {
            $perPage = 150;
        }

        $filtros = [
            'buscar' => $input->buscar,
            'estado' => $input->estado,
        ];

        return $this->ordenRepository->listarAgendasDetalladas($filtros, $perPage);
    }
}
