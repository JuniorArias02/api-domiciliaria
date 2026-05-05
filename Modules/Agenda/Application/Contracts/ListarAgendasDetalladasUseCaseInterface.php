<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Agenda\Application\DTO\PaginacionAgendaInputDTO;

interface ListarAgendasDetalladasUseCaseInterface
{
    public function execute(PaginacionAgendaInputDTO $input): LengthAwarePaginator;
}
