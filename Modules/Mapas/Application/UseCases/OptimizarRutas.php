<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Modules\Mapas\Infrastructure\Services\RutaOptimizationService;
use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;

class OptimizarRutas
{
    private $repo;
    private $optimizationService;
    private $servicioRepo;

    public function __construct(
        MapaRepositoryInterface $repo,
        RutaOptimizationService $optimizationService,
        ServicioRepositoryInterface $servicioRepo
    ) {
        $this->repo = $repo;
        $this->optimizationService = $optimizationService;
        $this->servicioRepo = $servicioRepo;
    }

    /**
     * Ejecuta la optimización de rutas según el tipo de filtro y el mes.
     * 
     * @param array $params ['mes' => int, 'tipo_filtro' => string, 'anio' => int]
     * @return array
     */
    public function execute(array $params): array
    {
        if (empty($params['mes'])) {
            throw new \InvalidArgumentException("El parámetro 'mes' es obligatorio.");
        }

        if (!empty($params['id_servicio'])) {
            $servicio = $this->servicioRepo->obtenerPorId((int) $params['id_servicio']);
            if (!$servicio) {
                throw new \InvalidArgumentException("El servicio seleccionado no existe o no es válido.");
            }
        }

        $mes = (int) $params['mes'];
        $anio = (int) ($params['anio'] ?? date('Y'));
        $tipoFiltro = $params['tipo_filtro'] ?? 'pacientes';
        $verAgendados = filter_var($params['ver_agendados'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($verAgendados) {
            // 1. Obtener candidatos del mes (Datos programados desde el repo)
            $candidatos = $this->repo->obtenerDatosBaseOptimizacion($params);
        } else {
            // 2. Obtener candidatos por predicción (basado en última visita y frecuencia)
            $datosBase = $this->repo->obtenerDatosBasePrediccion($params);

            // Predecir las próximas fechas
            $candidatos = $this->optimizationService->predecirFechasPorFrecuencia($datosBase, $mes, $anio);
        }

        if (empty($candidatos)) {
            return [];
        }

        if ($tipoFiltro === 'profesional') {
            return $this->optimizarPorProfesional($candidatos);
        }

        // Por defecto: Optimización por Pacientes (Global por cercanía)
        return $this->optimizarPorPacientes($candidatos);
    }

    /**
     * Optimiza las rutas agrupándolas por profesional.
     */
    private function optimizarPorProfesional(array $candidatos): array
    {
        $agrupados = [];
        foreach ($candidatos as $c) {
            $idPro = is_object($c) ? ($c->id_personal ?? 0) : ($c['id_personal'] ?? 0);
            $agrupados[$idPro][] = is_object($c) ? $c : (object) $c;
        }

        $resultadoFinal = [];
        $bloqueGlobal = 1;

        foreach ($agrupados as $idPro => $pacientesPro) {
            // Optimizar cada grupo de profesional por cercanía
            $ordenados = $this->optimizationService->optimizarPorVecinoCercano($pacientesPro, true);
            // Agrupar en bloques de 8
            $bloques = $this->optimizationService->agruparEnBloques($ordenados, 8);
            
            $maxBloqueLocal = 0;
            foreach ($bloques as $b) {
                $b['bloque_ruta'] = $b['bloque_ruta'] + $bloqueGlobal - 1;
                $b['id_profesional'] = $idPro;
                $resultadoFinal[] = $b;

                if (($b['bloque_ruta'] - $bloqueGlobal + 1) > $maxBloqueLocal) {
                    $maxBloqueLocal = ($b['bloque_ruta'] - $bloqueGlobal + 1);
                }
            }
            $bloqueGlobal += $maxBloqueLocal;
        }

        return $resultadoFinal;
    }

    /**
     * Optimiza las rutas de forma global por cercanía entre pacientes.
     */
    private function optimizarPorPacientes(array $candidatos): array
    {
        $objetos = array_map(fn($c) => (object) $c, $candidatos);
        $ordenados = $this->optimizationService->optimizarPorVecinoCercano($objetos, true);
        return $this->optimizationService->agruparEnBloques($ordenados, 8);
    }
}
