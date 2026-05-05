<?php

declare(strict_types=1);

namespace Modules\Agenda\Application\Contracts;

use Modules\Agenda\Application\DTO\CrearAgendaMasivaInputDTO;

interface CrearAgendaMasivaUseCaseInterface
{
    /**
     * @param CrearAgendaMasivaInputDTO $input
     * @return void
     */
    public function execute(CrearAgendaMasivaInputDTO $input): void;
}
