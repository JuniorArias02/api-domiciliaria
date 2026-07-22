<?php

namespace Modules\Gateway\Application\Services;

use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Modules\Gateway\Domain\Contracts\GatewayRepositoryInterface;
use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;

class FiltroServiciosService
{
    private $servicioRepository;
    private $personalRepository;
    private $ingresoRepository;
    private $gatewayRepository;
    private $cargosRepository;

    public function __construct(
        ServicioRepositoryInterface $servicioRepository,
        PersonalRepositoryInterface $personalRepository,
        IngresoRepositoryInterface $ingresoRepository,
        GatewayRepositoryInterface $gatewayRepository,
        CargosRepositoryInterface $cargosRepository
    ) {
        $this->servicioRepository = $servicioRepository;
        $this->personalRepository = $personalRepository;
        $this->ingresoRepository = $ingresoRepository;
        $this->gatewayRepository = $gatewayRepository;
        $this->cargosRepository = $cargosRepository;
    }

    /**
     * Filtra los detalles de un ingreso aplicando 3 reglas:
     * 1. El ingresoId no debe existir en el sistema.
     * 2. El código del servicio debe existir localmente.
     * 3. El profesional (nombreUsr) debe existir de forma exacta.
     *
     * @param array $detallesIngreso
     * @return array
     */
    public function filtrarPorServiciosLocales(array $detallesIngreso): array
    {
        if (empty($detallesIngreso)) {
            return [];
        }

        // Regla 1: Validar que el ingreso no esté en el sistema
        $primerDetalle = reset($detallesIngreso);
        if (isset($primerDetalle['ingresoId'])) {
            $ingresoId = $primerDetalle['ingresoId'];
            if ($this->ingresoRepository->existePorNumeroIngreso((int) $ingresoId)) {
                return []; // Si ya existe, no devolvemos nada
            }
        }

        // Obtener la lista de códigos de servicio válidos desde la base de datos
        $codigosValidos = $this->servicioRepository->obtenerCodigosServicios();
        $detallesFiltrados = [];

        foreach ($detallesIngreso as $key => $detalle) {
            // Regla 2: Verificar si el codigoProd está en los válidos
            if (!isset($detalle['codigoProd']) || !in_array($detalle['codigoProd'], $codigosValidos)) {
                continue; // No pasa el filtro de servicio
            }

            // Regla 3: El profesional debe existir en la BD. Si no, se busca y se crea.
            if (!isset($detalle['nombreUsr'])) {
                continue; // No tiene profesional
            }

            $nombreUsr = $detalle['nombreUsr'];
            $idPersonal = $this->personalRepository->obtenerIdPorNombre($nombreUsr);

            if ($idPersonal === null) {
                try {
                    $gatewayResponse = $this->gatewayRepository->buscarUsuarioPorNombre($nombreUsr);
                    if (!empty($gatewayResponse['content'])) {
                        $profesionalGateway = $gatewayResponse['content'][0];
                        
                        // Validar si existe el rol (cargo), si no, se crea
                        $nombreCargo = $profesionalGateway['nombreEsp'] ?? 'MEDICO GENERAL';
                        $cargo = $this->cargosRepository->obtenerPorNombre($nombreCargo);
                        
                        if (!$cargo) {
                            $cargo = $this->cargosRepository->crear(['nombre' => $nombreCargo]);
                        }

                        // Crear el profesional
                        $nuevoPersonal = $this->personalRepository->crear([
                            'id_cargo' => $cargo->id_cargo,
                            'nombre_completo' => $profesionalGateway['nombreUsr'],
                            'numero_documento' => $profesionalGateway['codigoUsr'] ?? null,
                            'tipo_documento' => 'CC',
                            'estado' => 1
                        ]);
                        
                        $idPersonal = $nuevoPersonal->id_personal;
                    }
                } catch (\Exception $e) {
                    // Si el gateway falla o el usuario no existe, se ignora
                    continue;
                }
            }

            if ($idPersonal !== null) {
                // Agregar el ID del profesional encontrado para facilitar el trabajo más adelante
                $detalle['id_personal'] = $idPersonal;
                $detallesFiltrados[$key] = $detalle;
            }
        } 

        return $detallesFiltrados;
    }
}
