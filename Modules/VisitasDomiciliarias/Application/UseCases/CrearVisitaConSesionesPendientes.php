<?php

namespace Modules\VisitasDomiciliarias\Application\UseCases;

use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Exception;

class CrearVisitaConSesionesPendientes
{
    private $visitaRepo;
    private $ordenServicioRepo;

    public function __construct(
        VisitaDomiciliariaRepositoryInterface $visitaRepo,
        OrdenServicioRepositoryInterface $ordenServicioRepo
    ) {
        $this->visitaRepo = $visitaRepo;
        $this->ordenServicioRepo = $ordenServicioRepo;
    }

    public function execute(array $data)
    {
        if (empty($data['id_orden_servicio'])) {
            throw new Exception("El campo id_orden_servicio es requerido", 400);
        }

        $ordenServicio = $this->ordenServicioRepo->obtenerPorId($data['id_orden_servicio']);
        if (!$ordenServicio) {
            throw new Exception("La orden de servicio especificada no existe", 404);
        }

        $visitasActivas = $this->visitaRepo->contarVisitasActivasPorOrdenServicio($ordenServicio->id_orden_servicio);

        if ($visitasActivas >= $ordenServicio->numero_sesiones) {
            throw new Exception("El servicio seleccionado ya no tiene sesiones pendientes", 400);
        }

        if (empty($data['id_paciente'])) {
            throw new Exception("El campo id_paciente es requerido por la base de datos", 400);
        }
        if (empty($data['id_personal'])) {
            throw new Exception("El campo id_personal es requerido por la base de datos", 400);
        }
        if (empty($data['fecha_programada'])) {
            throw new Exception("El campo fecha_programada es requerido", 400);
        }

        return $this->visitaRepo->crear($data);
    }
}
