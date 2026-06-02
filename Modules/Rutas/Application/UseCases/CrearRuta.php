<?php

namespace Modules\Rutas\Application\UseCases;

use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class CrearRuta
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

    public function execute(array $data)
    {
        if (empty($data['id_personal'])) {
            throw new Exception("El profesional (id_personal) es obligatorio", 400);
        }

        if (empty($data['fecha_ruta'])) {
            throw new Exception("La fecha de la ruta (fecha_ruta) es obligatoria", 400);
        }

        // Validar formato de fecha (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_ruta'])) {
            throw new Exception("La fecha de la ruta debe tener el formato YYYY-MM-DD", 400);
        }

        // Verificar si el profesional existe
        $personal = $this->personalRepo->obtenerPorId($data['id_personal']);
        if (!$personal) {
            throw new Exception("El profesional especificado no existe", 404);
        }

        // Validar si ya existe una ruta para el profesional en la misma fecha
        if ($this->repo->existeRutaParaPersonalEnFecha($data['id_personal'], $data['fecha_ruta'])) {
            throw new Exception("Ya existe una ruta asignada a este profesional para la fecha especificada", 400);
        }

        // Validar el estado si se proporciona
        if (isset($data['estado'])) {
            $allowedStates = ['EN_DISENO', 'ASIGNADA', 'EN_RECORRIDO', 'FINALIZADA'];
            if (!in_array($data['estado'], $allowedStates)) {
                throw new Exception("El estado no es válido. Valores permitidos: " . implode(', ', $allowedStates), 400);
            }
        } else {
            $data['estado'] = 'EN_DISENO';
        }

        $visitasData = $data['visitas'] ?? [];
        if (!is_array($visitasData)) {
            throw new Exception("El campo visitas debe ser un arreglo", 400);
        }

        // Validar las visitas y sus pacientes antes de iniciar la transacción
        $pacientesEnRuta = [];
        $visitasParaProcesar = [];

        foreach ($visitasData as $item) {
            if (empty($item['id_visita'])) {
                throw new Exception("Cada visita debe tener un id_visita", 400);
            }

            $visita = $this->visitaRepo->obtenerPorId($item['id_visita']);
            if (!$visita) {
                throw new Exception("La visita con ID {$item['id_visita']} no existe", 404);
            }

            // Validar si el paciente ya está seleccionado en la misma ruta (solicitud actual)
            if (in_array($visita->id_paciente, $pacientesEnRuta)) {
                throw new Exception("El paciente con ID {$visita->id_paciente} ya está seleccionado en esta ruta", 400);
            }
            $pacientesEnRuta[] = $visita->id_paciente;

            // Validar si el paciente ya tiene una visita asignada a otra ruta en la misma fecha
            if ($this->visitaRepo->existeVisitaAsignadaAPacienteEnFecha($visita->id_paciente, $data['fecha_ruta'])) {
                throw new Exception("El paciente con ID {$visita->id_paciente} ya tiene una visita asignada a otra ruta en la misma fecha", 400);
            }

            $visitasParaProcesar[] = [
                'visita' => $visita,
                'orden_visita' => $item['orden_visita'] ?? null
            ];
        }

        // Ejecutar todo en una transacción de base de datos
        return DB::transaction(function () use ($data, $visitasParaProcesar) {
            // 1. Crear la cabecera de la ruta
            $ruta = $this->repo->crear([
                'id_personal' => $data['id_personal'],
                'fecha_ruta' => $data['fecha_ruta'],
                'estado' => $data['estado']
            ]);

            // 2. Asociar y ordenar cada visita
            foreach ($visitasParaProcesar as $item) {
                $visita = $item['visita'];
                
                // Asignar id_ruta y orden_visita
                $visita->update([
                    'id_ruta' => $ruta->id_ruta,
                    'orden_visita' => $item['orden_visita']
                ]);
            }

            // Retornamos la ruta con sus visitas actualizadas
            return $this->repo->obtenerPorId($ruta->id_ruta);
        });
    }
}
