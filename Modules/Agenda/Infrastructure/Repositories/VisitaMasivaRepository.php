<?php

declare(strict_types=1);

namespace Modules\Agenda\Infrastructure\Repositories;

use Modules\Agenda\Domain\Contracts\VisitaRepositoryInterface;
use Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria;

class VisitaMasivaRepository implements VisitaRepositoryInterface
{
    public function insertarMasivamente(array $visitas): bool
    {
        if (empty($visitas)) {
            return false;
        }

        // Eloquent insert() ejecutará un único `INSERT INTO visitas_domiciliarias (...) VALUES (...), (...)`
        // Si hay una falla lanzará \Illuminate\Database\QueryException que será atrapada y devuelta como rollback por `DB::transaction`.
        return VisitaDomiciliaria::insert($visitas);
    }
}
