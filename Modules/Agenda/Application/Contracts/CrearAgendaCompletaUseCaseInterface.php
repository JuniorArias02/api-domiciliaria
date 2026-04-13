<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\Contracts;

use Modules\Agenda\Application\DTO\AgendaInputDTO;

interface CrearAgendaCompletaUseCaseInterface
{
    public function execute(AgendaInputDTO $input): void;
}
