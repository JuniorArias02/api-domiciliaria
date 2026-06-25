<?php

namespace Modules\VisitasDomiciliarias\Application\UseCases;

use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Exception;

class CompletarVisitaDomiciliaria
{
    private $repo;
    private $ordenServicioRepo;
    private $ordenMedicaRepo;
    private $rutaRepo;

    public function __construct(
        VisitaDomiciliariaRepositoryInterface $repo,
        OrdenServicioRepositoryInterface $ordenServicioRepo,
        OrdenMedicaRepositoryInterface $ordenMedicaRepo,
        RutaRepositoryInterface $rutaRepo
    ) {
        $this->repo = $repo;
        $this->ordenServicioRepo = $ordenServicioRepo;
        $this->ordenMedicaRepo = $ordenMedicaRepo;
        $this->rutaRepo = $rutaRepo;
    }

    public function execute(int $idVisita)
    {
        $visita = $this->repo->obtenerPorId($idVisita);
        if (!$visita) {
            throw new Exception("Visita domiciliaria no encontrada", 404);
        }

        // 1. Completar la visita
        $visitaActualizada = $this->repo->actualizar($idVisita, [
            'fecha_realizada' => now()->toDateTimeString(),
            'estado' => 'COMPLETADA'
        ]);

        // 2. Si tiene una orden de servicio asociada, validar si se debe completar
        if ($visitaActualizada->id_orden_servicio) {
            $ordenServicio = $this->ordenServicioRepo->obtenerPorId($visitaActualizada->id_orden_servicio);
            if ($ordenServicio) {
                // Contar las visitas completadas asociadas a esta orden de servicio
                $visitasCompletadasCount = $ordenServicio->visitas()->where('estado', 'COMPLETADA')->count();

                // Si las visitas completadas son iguales o mayores al número de sesiones
                if ($visitasCompletadasCount >= $ordenServicio->numero_sesiones) {
                    $this->ordenServicioRepo->actualizar($ordenServicio->id_orden_servicio, [
                        'estado' => 'COMPLETADA'
                    ]);

                    // 3. Validar si todas las órdenes de servicio de esta Orden Médica están COMPLETADAS
                    if ($ordenServicio->id_orden) {
                        $ordenMedica = $this->ordenMedicaRepo->obtenerPorId($ordenServicio->id_orden);
                        if ($ordenMedica) {
                            $todosCompletados = true;
                            foreach ($ordenMedica->servicios as $servicio) {
                                // Si es la orden de servicio actual, ya sabemos que se acaba de actualizar a COMPLETADA
                                if ($servicio->id_orden_servicio === $ordenServicio->id_orden_servicio) {
                                    continue;
                                }
                                if ($servicio->estado !== 'COMPLETADA') {
                                    $todosCompletados = false;
                                    break;
                                }
                            }

                            if ($todosCompletados) {
                                $this->ordenMedicaRepo->actualizar($ordenMedica->id_orden, [
                                    'estado' => 'FINALIZADA'
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // 4. Validar si todas las visitas de la ruta asociada están completadas
        if ($visitaActualizada->id_ruta) {
            $ruta = $this->rutaRepo->obtenerPorId($visitaActualizada->id_ruta);
            if ($ruta && $ruta->visitas) {
                $todasCompletadas = true;
                foreach ($ruta->visitas as $visitaRuta) {
                    // Si encontramos una visita de la ruta que no está COMPLETADA (ignorando la que se acaba de actualizar)
                    if ($visitaRuta->id_visita !== $idVisita && $visitaRuta->estado !== 'COMPLETADA' && $visitaRuta->estado !== 'CANCELADA') {
                        $todasCompletadas = false;
                        break;
                    }
                }

                if ($todasCompletadas) {
                    $this->rutaRepo->actualizar($ruta->id_ruta, [
                        'estado' => 'FINALIZADA'
                    ]);
                }
            }
        }

        return $visitaActualizada;
    }
}
