<?php

namespace Modules\Departamentos\Infrastructure\Repositories;

use Modules\Departamentos\Domain\Contracts\DepartamentoRepositoryInterface;
use Modules\Departamentos\Infrastructure\Models\Departamento;

class DepartamentoRepository implements DepartamentoRepositoryInterface
{
    public function listar()
    {
        return Departamento::all();
    }
}
