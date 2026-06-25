<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class EditarRuta
{
    private $repo;
    private $personalRepo;
    private $visitaRepo;

    public function __construct(
        RutaRepositoryInterface $repo,
        PersonalRepositoryInterface $personalRepo,
        VisitaDomiciliariaRepositoryInterface $visitaRepo
    ) {
        $this->repo = $repo;
        $this->personalRepo = $personalRepo;
        $this->visitaRepo = $visitaRepo;
    }

    public function execute(int $idRuta, array $data)
    {
        $ruta = $this->repo->obtenerPorId($idRuta);
        if (!$ruta) {
            throw new Exception("La ruta especificada no existe", 404);
        }

        // Si se va a cambiar el personal o la fecha, validamos
        $nuevoIdPersonal = $data['id_personal'] ?? $ruta->id_personal;
        $nuevaFecha = $data['fecha_ruta'] ?? $ruta->fecha_ruta;

        if ($nuevoIdPersonal != $ruta->id_personal || $nuevaFecha != $ruta->fecha_ruta) {
            if ($this->repo->existeRutaParaPersonalEnFecha($nuevoIdPersonal, $nuevaFecha)) {
                throw new Exception("Ya existe una ruta asignada a este profesional para la fecha especificada", 400);
            }
        }

        // Si se envió la llave id_personal o fecha_ruta, validamos su existencia/formato
        if (isset($data['id_personal'])) {
            $personal = $this->personalRepo->obtenerPorId($data['id_personal']);
            if (!$personal) {
                throw new Exception("El profesional especificado no existe", 404);
            }
        }

        if (isset($data['fecha_ruta'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_ruta'])) {
                throw new Exception("La fecha de la ruta debe tener el formato YYYY-MM-DD", 400);
            }
        }

        $visitasData = $data['visitas'] ?? null;
        $visitasParaProcesar = [];

        if (is_array($visitasData)) {
            $pacientesEnRuta = [];
            foreach ($visitasData as $item) {
                if (empty($item['id_visita'])) {
                    throw new Exception("Cada visita debe tener un id_visita", 400);
                }

                $visita = $this->visitaRepo->obtenerPorId($item['id_visita']);
                if (!$visita) {
                    throw new Exception("La visita con ID {$item['id_visita']} no existe", 404);
                }

                // Validar si la visita ya pertenece a otra ruta
                if (!is_null($visita->id_ruta) && $visita->id_ruta != $idRuta) {
                    throw new Exception("La visita con ID {$item['id_visita']} ya está asignada a otra ruta", 400);
                }

                // Validar duplicidad de pacientes en la misma ruta
                if (in_array($visita->id_paciente, $pacientesEnRuta)) {
                    throw new Exception("El paciente con ID {$visita->id_paciente} está duplicado en la solicitud", 400);
                }
                $pacientesEnRuta[] = $visita->id_paciente;

                $visitasParaProcesar[] = [
                    'visita' => $visita,
                    'orden_visita' => $item['orden_visita'] ?? null
                ];
            }
        }

        // Ejecutar todo en una transacción de base de datos
        return DB::transaction(function () use ($idRuta, $data, $visitasData, $visitasParaProcesar, $ruta) {
            // Actualizar datos de cabecera si vienen en el request
            $updateData = [];
            if (isset($data['id_personal'])) $updateData['id_personal'] = $data['id_personal'];
            if (isset($data['fecha_ruta'])) $updateData['fecha_ruta'] = $data['fecha_ruta'];
            if (isset($data['estado'])) $updateData['estado'] = $data['estado'];

            if (!empty($updateData)) {
                $this->repo->actualizar($idRuta, $updateData);
            }

            // Si se envió un array de visitas, reconfiguramos las visitas de la ruta
            if (is_array($visitasData)) {
                // 1. Desvincular todas las visitas actuales de la ruta
                foreach ($ruta->visitas as $visitaActual) {
                    $this->visitaRepo->actualizar($visitaActual->id_visita, [
                        'id_ruta' => null,
                        'orden_visita' => null
                    ]);
                }

                // 2. Asociar las nuevas visitas con su orden
                foreach ($visitasParaProcesar as $item) {
                    $visita = $item['visita'];
                    $this->visitaRepo->actualizar($visita->id_visita, [
                        'id_ruta' => $idRuta,
                        'orden_visita' => $item['orden_visita']
                    ]);
                }
            }

            return $this->repo->obtenerPorId($idRuta);
        });
    }
}
