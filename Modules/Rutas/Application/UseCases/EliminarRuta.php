<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class EliminarRuta
{
    private $repo;
    private $visitaRepo;

    public function __construct(
        RutaRepositoryInterface $repo,
        VisitaDomiciliariaRepositoryInterface $visitaRepo
    ) {
        $this->repo = $repo;
        $this->visitaRepo = $visitaRepo;
    }

    public function execute(int $idRuta)
    {
        $ruta = $this->repo->obtenerPorId($idRuta);
        if (!$ruta) {
            throw new Exception("La ruta especificada no existe", 404);
        }

        // Si la ruta ya está finalizada, quizás no debería poder eliminarse
        if ($ruta->estado === 'FINALIZADA') {
            throw new Exception("No se puede eliminar una ruta que ya ha sido finalizada", 400);
        }

        return DB::transaction(function () use ($idRuta, $ruta) {
            // 1. Desvincular todas las visitas asociadas a esta ruta
            foreach ($ruta->visitas as $visitaActual) {
                $this->visitaRepo->actualizar($visitaActual->id_visita, [
                    'id_ruta' => null,
                    'orden_visita' => null
                ]);
            }

            // 2. Eliminar la ruta
            $this->repo->eliminar($idRuta);

            return true;
        });
    }
}
