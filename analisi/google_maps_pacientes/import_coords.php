<?php

/**
 * 💾 STEP 3: IMPORTACIÓN DE COORDENADAS A LA BD
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jsonPath = __DIR__ . '/resultados_coords.json';
if (!file_exists($jsonPath)) {
    die("❌ Error: No se encontró 'resultados_coords.json'.\n");
}

$data = json_decode(file_get_contents($jsonPath), true);
$total = count($data);

echo "💾 Iniciando actualización masiva de $total pacientes en la BD...\n";

$actualizados = 0;
foreach (array_chunk($data, 500) as $chunk) {
    try {
        DB::beginTransaction();
        foreach ($chunk as $row) {
            if (isset($row['latitud']) && isset($row['longitud'])) {
                DB::table('pacientes')
                    ->where('id_paciente', $row['id_paciente'])
                    ->update([
                        'latitud'          => $row['latitud'],
                        'longitud'         => $row['longitud'],
                        'url_google_maps'  => $row['url_maps'] ?? null,
                        'updated_at'       => now()
                    ]);
                $actualizados++;
            }
        }
        DB::commit();
        echo "✅ " . ($actualizados) . " registros actualizados...\n";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "❌ Error en bloque: " . $e->getMessage() . "\n";
    }
}

echo "\n🏁 IMPORTACIÓN FINALIZADA.\n";
echo "📦 Total registros actualizados: $actualizados\n";
echo "🚀 ¡Ya puedes ver a los pacientes en tu mapa!\n";
