<?php

namespace Modules\Municipios\Infrastructure\Repositories;

use Modules\Municipios\Domain\Contracts\MunicipioRepositoryInterface;
use Modules\Municipios\Infrastructure\Models\Municipio;

class MunicipioRepository implements MunicipioRepositoryInterface
{
    public function listar()
    {
        return Municipio::with('departamento')->get();
    }
}
