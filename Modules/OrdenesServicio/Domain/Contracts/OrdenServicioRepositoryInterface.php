<?php

namespace Modules\OrdenesServicio\Domain\Contracts;

interface OrdenServicioRepositoryInterface
{
    public function listar();
    public function crear(array $data);
}
