<?php

namespace Modules\Mapas\Infrastructure\Services;

use Modules\Mapas\Infrastructure\Services\CalculadoraDistanciaService;

/**
 * Arquitecto de Software: RutaOptimizationService
 * Responsabilidad ÚNICA: Lógica pesada, algoritmos de ordenamiento y agrupamiento geográfico.
 * PROHIBIDO: Acceso directo a Base de Datos.
 */
class RutaOptimizationService
{
    protected $calculadoraDistancia;

    public function __construct(CalculadoraDistanciaService $calculadoraDistancia)
    {
        $this->calculadoraDistancia = $calculadoraDistancia;
    }

    /**
     * Aplica el algoritmo de "Vecino más cercano" (Nearest Neighbor) a una lista de puntos.
     */
    public function optimizarPorVecinoCercano(array $lista, bool $usarHaversine = false): array
    {
        if (empty($lista)) return [];

        $rutaOrdenada = [];
        $pendientes = $lista;

        // Empezamos con el primer elemento
        $actual = array_shift($pendientes);
        $rutaOrdenada[] = $actual;

        while (!empty($pendientes)) {
            $mejorIndice = null;
            $menorDistancia = INF;

            foreach ($pendientes as $i => $candidato) {
                // Convertimos a array si es objeto para uniformidad
                $lat1 = is_object($actual) ? $actual->latitud : $actual['latitud'];
                $lon1 = is_object($actual) ? $actual->longitud : $actual['longitud'];
                $lat2 = is_object($candidato) ? $candidato->latitud : $candidato['latitud'];
                $lon2 = is_object($candidato) ? $candidato->longitud : $candidato['longitud'];

                $dist = $usarHaversine 
                    ? $this->calculadoraDistancia->haversine($lat1, $lon1, $lat2, $lon2)
                    : $this->calculadoraDistancia->calcularDistanciaEuclidiana($lat1, $lon1, $lat2, $lon2);

                if ($dist < $menorDistancia) {
                    $menorDistancia = $dist;
                    $mejorIndice = $i;
                }
            }

            $actual = $pendientes[$mejorIndice];
            $rutaOrdenada[] = $actual;
            array_splice($pendientes, $mejorIndice, 1);
        }

        return $rutaOrdenada;
    }

    /**
     * Fragmenta una ruta en bloques de tamaño N (por defecto 8).
     */
    public function agruparEnBloques(array $ruta, int $tamanoBloque = 8): array
    {
        $resultado = [];
        $grupos = array_chunk($ruta, $tamanoBloque);

        foreach ($grupos as $idxGrupo => $grupo) {
            $bloqueNumero = $idxGrupo + 1;
            foreach ($grupo as $idxVisita => $item) {
                // Si es objeto, lo convertimos a array para manipularlo o usamos setters si tuviera
                $data = (array) $item;
                $data['bloque_ruta'] = $bloqueNumero;
                $data['orden_en_ruta'] = $idxVisita + 1;
                $data['orden_global'] = ($idxGrupo * $tamanoBloque) + ($idxVisita + 1);
                
                $resultado[] = $data;
            }
        }

        return $resultado;
    }

    /**
     * Procesa una colección de visitas para asignarles un orden secuencial diario.
     */
    public function asignarOrdenVisitaDiario(array $visitas): array
    {
        $dailyCounter = [];
        $procesados = [];

        foreach ($visitas as $visita) {
            $item = (array) $visita;
            $fullDate = $item['fecha_realizada'];
            $dateOnly = date('Y-m-d', strtotime($fullDate));

            if (!isset($dailyCounter[$dateOnly])) {
                $dailyCounter[$dateOnly] = 1;
            } else {
                $dailyCounter[$dateOnly]++;
            }

            $item['orden_visita'] = $dailyCounter[$dateOnly];
            $item['fecha_realizada'] = $dateOnly;
            
            $procesados[] = $item;
        }

        return $procesados;
    }

    /**
     * Lógica de predicción de fechas basada en frecuencia (Reemplaza lógica manual en Repo).
     */
    public function predecirFechasPorFrecuencia(array $datos, int $mes, int $anio): array
    {
        $candidatos = [];

        foreach ($datos as $row) {
            $item = (array) $row;
            
            $frecuencia = (int) ($item['frecuencia_dias'] ?? 30);
            if ($frecuencia <= 0) {
                $frecuencia = 30;
            }

            $realizadas = (int) ($item['sesiones_completadas'] ?? 0);
            $numeroSesiones = (int) ($item['numero_sesiones'] ?? 0);

            // Determinar fecha base
            $fechaOrden = !empty($item['fecha_inicio']) ? new \DateTime($item['fecha_inicio']) : (!empty($item['fecha_orden']) ? new \DateTime($item['fecha_orden']) : null);
            
            if (!empty($item['ultima_visita'])) {
                $fechaUltima = new \DateTime($item['ultima_visita']);
                if ($fechaOrden) {
                    $fechaBase = ($fechaUltima > $fechaOrden) ? $fechaUltima : $fechaOrden;
                } else {
                    $fechaBase = $fechaUltima;
                }
            } else {
                $fechaBase = $fechaOrden;
            }

            if (!$fechaBase) {
                continue;
            }

            // Si ya completó las sesiones de la orden, proyectamos únicamente la siguiente sesión virtual (1 sola)
            // Si todavía tiene pendientes, proyectamos las restantes de esta orden
            if ($realizadas >= $numeroSesiones) {
                $maxProyecciones = 1;
            } else {
                $maxProyecciones = $numeroSesiones - $realizadas;
            }

            $fechaCalculo = clone $fechaBase;

            for ($k = 0; $k < $maxProyecciones; $k++) {
                $fechaCalculo->modify("+{$frecuencia} days");

                // Validar si la fecha proyectada cae en el mes y año solicitado
                if ((int)$fechaCalculo->format('m') === $mes && (int)$fechaCalculo->format('Y') === $anio) {
                    $virtualVisit = $item;
                    $virtualVisit['virtual'] = true;
                    $virtualVisit['estado'] = 'PENDIENTE';
                    $virtualVisit['fecha_proyectada'] = $fechaCalculo->format('Y-m-d');
                    $virtualVisit['fecha_programada'] = $fechaCalculo->format('Y-m-d');
                    $virtualVisit['sesion_n'] = $realizadas + $k + 1;
                    $candidatos[] = $virtualVisit;
                }
            }
        }

        return $candidatos;
    }
}
