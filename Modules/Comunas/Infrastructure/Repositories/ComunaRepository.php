<?php

namespace Modules\Comunas\Infrastructure\Repositories;

use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;
use Modules\Comunas\Infrastructure\Models\Comuna;

class ComunaRepository implements ComunaRepositoryInterface
{
    public function listar()
    {
        return Comuna::with('municipio')->get();
    }
}
