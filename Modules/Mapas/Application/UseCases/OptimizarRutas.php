<?php

namespace Modules\Mapas\Application\UseCases;

use Modules\Mapas\Domain\Contracts\MapaRepositoryInterface;
use Modules\Mapas\Infrastructure\Services\RutaOptimizationService;

class OptimizarRutas
{
    private $repo;
    private $optimizationService;

    public function __construct(
        MapaRepositoryInterface $repo,
        RutaOptimizationService $optimizationService
    ) {
        $this->repo = $repo;
        $this->optimizationService = $optimizationService;
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

        $mes = (int) $params['mes'];
        $anio = (int) ($params['anio'] ?? date('Y'));
        $tipoFiltro = $params['tipo_filtro'] ?? 'pacientes';

        // 1. Obtener candidatos del mes (Datos crudos desde el repo)
        $datosBase = $this->repo->obtenerDatosBaseOptimizacion($params);
        
        // 2. El service se encarga de calcular quiénes caen en el mes según su frecuencia
        $candidatos = $this->optimizationService->predecirFechasPorFrecuencia($datosBase, $mes, $anio);

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
            $idPro = $c['id_personal'] ?? 0; // Asumimos que id_personal viene en los datos
            $agrupados[$idPro][] = (object) $c;
        }

        $resultadoFinal = [];
        foreach ($agrupados as $idPro => $pacientesPro) {
            // Optimizar cada grupo de profesional por cercanía
            $ordenados = $this->optimizationService->optimizarPorVecinoCercano($pacientesPro, true);
            // Agrupar en bloques de 8
            $bloques = $this->optimizationService->agruparEnBloques($ordenados, 8);
            
            foreach ($bloques as $b) {
                $b['id_profesional'] = $idPro;
                $resultadoFinal[] = $b;
            }
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
