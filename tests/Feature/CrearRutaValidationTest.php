<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Rutas\Application\UseCases\CrearRuta;
use Modules\Rutas\Infrastructure\Models\Ruta;
use Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create base data
    // 1. Cargo
    $this->cargoId = DB::table('cargos')->insertGetId([
        'nombre' => 'Medico General',
        'created_at' => now()
    ]);

    // 2. Personal
    $this->personalId = DB::table('personal')->insertGetId([
        'id_cargo' => $this->cargoId,
        'nombre_completo' => 'Juan Perez',
        'numero_documento' => '12345678',
        'estado' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // 3. Aseguradora
    $this->aseguradoraId = DB::table('aseguradoras')->insertGetId([
        'nombre' => 'EPS Sanitas',
        'activa' => 1,
        'created_at' => now()
    ]);

    // 4. Pacientes
    $this->pacienteId1 = DB::table('pacientes')->insertGetId([
        'identificacion' => '10001',
        'nombre_completo' => 'Paciente Uno',
        'fecha_nacimiento' => '1990-01-01',
        'sexo' => 'M',
        'id_aseguradora' => $this->aseguradoraId,
        'direccion' => 'Calle 123',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    $this->pacienteId2 = DB::table('pacientes')->insertGetId([
        'identificacion' => '10002',
        'nombre_completo' => 'Paciente Dos',
        'fecha_nacimiento' => '1995-05-05',
        'sexo' => 'F',
        'id_aseguradora' => $this->aseguradoraId,
        'direccion' => 'Carrera 45',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Check if especialidades table exists and insert if it does
    if (Schema::hasTable('especialidades')) {
        $this->especialidadId = DB::table('especialidades')->insertGetId([
            'nombre' => 'Medicina General',
            'created_at' => now()
        ]);
    }

    // Check if servicios table exists and insert if it does
    if (Schema::hasTable('servicios')) {
        $this->servicioId = DB::table('servicios')->insertGetId([
            'codigo_servicio' => 'SRV001',
            'nombre_servicio' => 'Consulta Medica',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // Check if ordenes_servicios exists
    if (Schema::hasTable('ordenes_servicios')) {
        $this->ordenServicioId = DB::table('ordenes_servicios')->insertGetId([
            'id_orden' => 1,
            'id_servicio' => $this->servicioId ?? 1,
            'numero_sesiones' => 10,
            'estado' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // Helper to create visits based on actual schema columns
    $this->createVisita = function ($pacienteId) {
        $columns = Schema::getColumnListing('visitas_domiciliarias');
        $visitData = [
            'fecha_programada' => now()->toDateTimeString(),
            'estado' => 'PROGRAMADA',
            'created_at' => now(),
            'updated_at' => now()
        ];
        if (in_array('id_paciente', $columns)) {
            $visitData['id_paciente'] = $pacienteId;
        }
        if (in_array('id_personal', $columns)) {
            $visitData['id_personal'] = $this->personalId;
        }
        if (in_array('id_especialidad', $columns)) {
            $visitData['id_especialidad'] = $this->especialidadId ?? 1;
        }
        if (in_array('id_orden_servicio', $columns)) {
            $visitData['id_orden_servicio'] = $this->ordenServicioId ?? 1;
        }
        
        return DB::table('visitas_domiciliarias')->insertGetId($visitData);
    };
});

test('no permite crear ruta si ya existe una para el mismo profesional en la misma fecha', function () {
    $useCase = app(CrearRuta::class);

    // Crear primera ruta
    $useCase->execute([
        'id_personal' => $this->personalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => []
    ]);

    // Intentar crear segunda ruta para el mismo profesional y fecha
    expect(fn() => $useCase->execute([
        'id_personal' => $this->personalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => []
    ]))->toThrow(Exception::class, 'Ya existe una ruta asignada a este profesional para la fecha especificada');
});

test('no permite crear ruta con visitas del mismo paciente duplicado', function () {
    $useCase = app(CrearRuta::class);

    // Crear dos visitas para el mismo paciente 1
    $visitaId1 = ($this->createVisita)($this->pacienteId1);
    $visitaId2 = ($this->createVisita)($this->pacienteId1);

    // Intentar crear ruta con ambas visitas
    expect(fn() => $useCase->execute([
        'id_personal' => $this->personalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => [
            ['id_visita' => $visitaId1, 'orden_visita' => 1],
            ['id_visita' => $visitaId2, 'orden_visita' => 2]
        ]
    ]))->toThrow(Exception::class, "El paciente con ID {$this->pacienteId1} ya está seleccionado en esta ruta");
});

test('no permite crear ruta si el paciente ya tiene una visita asignada a otra ruta en la misma fecha', function () {
    $useCase = app(CrearRuta::class);

    // Crear visitas
    $visitaId1 = ($this->createVisita)($this->pacienteId1);
    $visitaId2 = ($this->createVisita)($this->pacienteId1);

    // 1. Crear una ruta y asignarle la visita 1 (de Paciente 1) para el 2026-06-01
    $useCase->execute([
        'id_personal' => $this->personalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => [
            ['id_visita' => $visitaId1, 'orden_visita' => 1]
        ]
    ]);

    // Creamos otro profesional para evitar la validación de profesional duplicado en la misma fecha
    $otroPersonalId = DB::table('personal')->insertGetId([
        'id_cargo' => $this->cargoId,
        'nombre_completo' => 'Otro Profesional',
        'numero_documento' => '87654321',
        'estado' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // 2. Intentar crear otra ruta para el 2026-06-01 con la visita 2 (también de Paciente 1)
    expect(fn() => $useCase->execute([
        'id_personal' => $otroPersonalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => [
            ['id_visita' => $visitaId2, 'orden_visita' => 1]
        ]
    ]))->toThrow(Exception::class, "El paciente con ID {$this->pacienteId1} ya tiene una visita asignada a otra ruta en la misma fecha");
});

test('puede listar todas las rutas', function () {
    $listarUseCase = app(Modules\Rutas\Application\UseCases\ListarRutas::class);
    $crearUseCase = app(CrearRuta::class);

    // Crear una ruta
    $crearUseCase->execute([
        'id_personal' => $this->personalId,
        'fecha_ruta' => '2026-06-01',
        'estado' => 'EN_DISENO',
        'visitas' => []
    ]);

    $rutas = $listarUseCase->execute();
    expect($rutas)->toHaveCount(1);
    expect($rutas[0]->id_personal)->toBe($this->personalId);
});
