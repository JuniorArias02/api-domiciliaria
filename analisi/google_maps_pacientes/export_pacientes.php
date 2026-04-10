<?php

/**
 * 🛰️ STEP 1: EXPORTE DE PACIENTES SIN COORDENADAS
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "📦 Extrayendo pacientes sin ubicación física...\n";

$pacientes = DB::table('pacientes')
    ->whereNull('latitud')
    ->orWhere('latitud', 0)
    ->select('id_paciente', 'nombre_completo', 'direccion')
    ->get()
    ->toArray();

$total = count($pacientes);
if ($total === 0) {
    die("✅ No hay pacientes pendientes de geocodificación.\n");
}

$jsonPath = __DIR__ . '/pendientes.json';
file_put_contents($jsonPath, json_encode($pacientes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "🛰️ Se han exportado $total pacientes a 'pendientes.json'.\n";
echo "👉 Ahora ejecuta: python analisi/google_maps_pacientes/geocoder.py\n";
