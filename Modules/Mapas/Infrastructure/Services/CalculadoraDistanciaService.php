<?php

namespace Modules\Mapas\Infrastructure\Services;

class CalculadoraDistanciaService
{
    /**
     * Calcula la distancia Haversine entre dos puntos (km).
     */
    public function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $R = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Calcula la distancia en línea recta entre dos coordenadas.
     */
    public function calcularDistanciaEuclidiana($lat1, $lon1, $lat2, $lon2): float
    {
        return sqrt(pow($lat1 - $lat2, 2) + pow($lon1 - $lon2, 2));
    }
}
