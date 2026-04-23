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
        $inicioMes = new \DateTime("$anio-$mes-01");
        $finMes = (clone $inicioMes)->modify('last day of this month');

        foreach ($datos as $row) {
            $item = (array) $row;
            $frecuencia = (int) ($item['frecuencia_dias'] ?? 30);
            if ($frecuencia <= 0) $frecuencia = 30;
            
            if (!empty($item['ultima_visita'])) {
                $baseDate = new \DateTime($item['ultima_visita']);
            } elseif (!empty($item['fecha_orden'])) {
                $baseDate = new \DateTime($item['fecha_orden']);
            } else {
                // Si no hay nada, no podemos predecir con exactitud, lo omitimos o usamos fallback
                continue; 
            }

            $proxima = clone $baseDate;
            
            // Si la base es anterior al mes, sumamos frecuencia hasta entrar al mes o pasarnos
            if ($proxima < $inicioMes) {
                while ($proxima < $inicioMes) {
                    $proxima->modify("+{$frecuencia} days");
                }
            } else {
                // Si la base ya está en el mes o después, la siguiente es base + frecuencia
                $proxima->modify("+{$frecuencia} days");
            }

            // Validar si la fecha proyectada cae en el mes solicitado
            if ((int)$proxima->format('m') === $mes && (int)$proxima->format('Y') === $anio) {
                $item['fecha_proyectada'] = $proxima->format('Y-m-d');
                $candidatos[] = $item;
            }
        }
        return $candidatos;
    }
}
