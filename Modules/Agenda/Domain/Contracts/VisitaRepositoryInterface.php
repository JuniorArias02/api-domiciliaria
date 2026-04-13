<?php

declare(strict_types=1);

namespace Modules\Agenda\Domain\Contracts;

interface VisitaRepositoryInterface
{
    /**
     * Inserta visitas de manera masiva optimizada.
     *
     * @param array $visitas Arreglo de arreglos asociativos
     * @return bool
     */
    public function insertarMasivamente(array $visitas): bool;
}
