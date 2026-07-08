<?php

namespace Modules\Continuidad\Domain\Contracts;

interface ContinuidadRepositoryInterface
{
    public function obtenerAlertasContinuidad(): array;
}
