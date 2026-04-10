<?php

/**
 * 🚀 SCRIPT DE IMPORTACIÓN MASIVA - VERSION REFORZADA (v2.1)
 */

use Illuminate\Support\Facades\DB;
use Modules\Servicios\Infrastructure\Models\Servicio;
use Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jsonPath = __DIR__ . '/data_import.json';
if (!file_exists($jsonPath)) {
    die("❌ Error: No se encontró data_import.json.\n");
}

$data = json_decode(file_get_contents($jsonPath), true);
$totalRows = count($data);
echo "📦 Procesando $totalRows registros con modo depuración...\n";

$stats = ['pacientes' => 0, 'servicios' => 0, 'visitas' => 0, 'dx' => 0, 'errores' => 0];
$cache = ['eps' => [], 'prof' => [], 'serv' => []];

foreach ($data as $index => $row) {
    try {
        DB::beginTransaction();

        // 1. VALIDACIÓN BÁSICA
        $identificacion = trim((string)($row['Beneficiario'] ?? ''));
        $codigoIngreso = trim((string)($row['Ingreso'] ?? ''));
        
        if (!$identificacion || !$codigoIngreso) {
            throw new Exception("Faltan campos clave (Beneficiario o Ingreso).");
        }

        // 2. ASEGURADORA
        $entidad = $row['Entidad'] ?? 'PARTICULAR';
        if (!isset($cache['eps'][$entidad])) {
            $cache['eps'][$entidad] = DB::table('aseguradoras')->where('nombre', 'LIKE', "%$entidad%")->value('id_aseguradora') ?: 1;
        }
        $idAseguradora = $cache['eps'][$entidad];

        // 3. SERVICIO
        $codServ = trim((string)($row['Servicio'] ?? ''));
        if ($codServ && !isset($cache['serv'][$codServ])) {
            $s = Servicio::where('codigo_servicio', $codServ)->first();
            if (!$s) {
                $s = Servicio::create([
                    'codigo_servicio' => $codServ,
                    'nombre_servicio' => $row['Descripción del Servicio'] ?? 'Servicio sin nombre',
                    'descripcion'     => 'Auto-creado desde Excel'
                ]);
                $stats['servicios']++;
            }
            $cache['serv'][$codServ] = $s->id_servicio;
        }
        $idServicio = $cache['serv'][$codServ] ?? null;

        // 4. PACIENTE
        $paciente = DB::table('pacientes')->where('identificacion', $identificacion)->first();
        if (!$paciente) {
            $idPaciente = DB::table('pacientes')->insertGetId([
                'tipo_documento'  => trim($row['TD'] ?? 'CC'),
                'identificacion'  => $identificacion,
                'nombre_completo' => trim($row['NombreBen'] ?? 'DESCONOCIDO'),
                'direccion'       => $row['Direccion'] ?? null,
                'telefono'        => $row['Telefono'] ?? null,
                'fecha_nacimiento' => !empty($row['Fecha Nac.']) ? date('Y-m-d', strtotime($row['Fecha Nac.'])) : null,
                'sexo'            => substr($row['Sexo'] ?? 'U', 0, 1),
                'id_aseguradora'  => $idAseguradora,
                'estado'          => 'ACTIVO',
                'created_at'      => now(),
                'updated_at'      => now()
            ]);
            $stats['pacientes']++;
        } else {
            $idPaciente = $paciente->id_paciente;
        }

        // 5. PROFESIONAL
        $profName = $row['Nombre Profesional'] ?? null;
        if ($profName && !isset($cache['prof'][$profName])) {
            $cache['prof'][$profName] = DB::table('personal')->where('nombre_completo', 'LIKE', "%$profName%")->value('id_personal');
        }
        $idPersonal = $cache['prof'][$profName] ?: 1;

        // 6. VISITA
        $visita = VisitaDomiciliaria::where('codigo_ingreso', $codigoIngreso)->first();
        if (!$visita) {
            $fechaAtencion = !empty($row['Fecha Atención']) ? date('Y-m-d H:i:s', strtotime($row['Fecha Atención'])) : now();
            $visita = VisitaDomiciliaria::create([
                'codigo_ingreso'    => $codigoIngreso,
                'id_paciente'       => $idPaciente,
                'id_personal'       => $idPersonal,
                'id_servicio'       => $idServicio,
                'id_especialidad'   => 1,
                'fecha_programada'  => $fechaAtencion,
                'fecha_realizada'   => $fechaAtencion,
                'tipo_atencion_ext' => $row['Tipo de Atención'] ?? null,
                'remitido_a'        => $row['Remitido A'] ?? null,
                'estado'            => 'COMPLETADA'
            ]);
            $stats['visitas']++;
        }

        // 7. DIAGNÓSTICOS
        $idVisita = $visita->id_visita;
        
        // Principal
        if (!empty($row['Dx sal.'])) {
            registrarDxRapido($idPaciente, $row['Dx sal.'], $idVisita, 1, $row['Descripción Diagnóstico'] ?? '');
            $stats['dx']++;
        }
        // Secundarios
        foreach (['Dx Rel. 1', 'Dx Rel. 2', 'Dx Rel. 3'] as $key) {
            if (!empty($row[$key])) {
                registrarDxRapido($idPaciente, $row[$key], $idVisita, 0);
                $stats['dx']++;
            }
        }

        DB::commit();
        if (($index + 1) % 500 == 0) echo "✅ " . ($index + 1) . " filas procesadas...\n";

    } catch (\Exception $e) {
        DB::rollBack();
        echo "❌ Error en fila " . ($index + 1) . " (Ingreso: " . ($row['Ingreso'] ?? 'N/A') . "): " . $e->getMessage() . "\n";
        $stats['errores']++;
        if ($stats['errores'] > 500) die("🛑 Demasiados errores consistentes. Deteniendo.\n");
    }
}

echo "\n🏁 RESUMEN FINAL:\n";
print_r($stats);

function registrarDxRapido($idP, $cie, $idV, $principal, $obs = '') {
    $cie = strtoupper(trim($cie));
    if (!$cie) return;

    // A. Asegurar que el CIE10 exista en la maestra
    $existsCie = DB::table('diagnosticos_cie10')->where('codigo', $cie)->exists();
    if (!$existsCie) {
        DB::table('diagnosticos_cie10')->insert([
            'codigo'      => $cie,
            'descripcion' => 'Importado de Excel',
            'activo'      => 1
        ]);
    }

    // B. Guardar la relación con el paciente para esta visita
    DB::table('paciente_diagnosticos')->updateOrInsert(
        ['id_paciente' => $idP, 'codigo_cie10' => $cie, 'tipo_diagnostico' => 'DOMICILIARIO', 'id_visita' => $idV],
        ['es_principal' => $principal, 'observacion' => $obs, 'fecha_registro' => now()]
    );
}
