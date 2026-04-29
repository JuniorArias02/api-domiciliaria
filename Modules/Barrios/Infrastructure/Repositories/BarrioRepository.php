<?php

namespace Modules\Barrios\Infrastructure\Repositories;

use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;
use Modules\Barrios\Infrastructure\Models\Barrio;

class BarrioRepository implements BarrioRepositoryInterface
{
    public function listar()
    {
        return Barrio::with(['municipio', 'comuna'])->get();
    }
}
