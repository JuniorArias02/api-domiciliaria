<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Esquema completo del sistema de atención domiciliaria.
     * Orden: tablas independientes → tablas con FK.
     */
    public function up(): void
    {
        // ================================================================
        // CATÁLOGOS INDEPENDIENTES (sin FK)
        // ================================================================

        Schema::create('zonas', function (Blueprint $table) {
            $table->increments('id_zona');
            $table->string('nombre', 50)->unique();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('aseguradoras', function (Blueprint $table) {
            $table->increments('id_aseguradora');
            $table->string('nombre', 100)->unique();
            $table->string('codigo_habilitacion', 20)->nullable();
            $table->tinyInteger('activa')->default(1);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('especialidades', function (Blueprint $table) {
            $table->increments('id_especialidad');
            $table->string('nombre', 100)->unique();
            $table->string('abreviatura', 10)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('diagnosticos_cie10', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->text('descripcion');
            $table->tinyInteger('activo')->default(1);
        });

        Schema::create('catalogo_equipos', function (Blueprint $table) {
            $table->increments('id_equipo');
            $table->string('nombre', 150)->unique();
            $table->string('categoria', 80)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('cargos', function (Blueprint $table) {
            $table->increments('id_cargo');
            $table->string('nombre', 100)->unique();
            $table->timestamp('created_at')->nullable();
        });

        // ================================================================
        // GEOGRAFÍA (zona → comuna → barrio)
        // ================================================================

        Schema::create('comunas', function (Blueprint $table) {
            $table->increments('id_comuna');
            $table->unsignedInteger('id_zona');
            $table->string('nombre', 50);
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_zona')->references('id_zona')->on('zonas');
        });

        Schema::create('barrios', function (Blueprint $table) {
            $table->increments('id_barrio');
            $table->unsignedInteger('id_comuna');
            $table->string('nombre', 150);
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_comuna')->references('id_comuna')->on('comunas');
        });

        // ================================================================
        // PERSONAL (depende de cargos y especialidades)
        // ================================================================

        Schema::create('personal', function (Blueprint $table) {
            $table->increments('id_personal');
            $table->unsignedInteger('id_cargo');
            $table->unsignedInteger('id_especialidad')->nullable();
            $table->string('nombre_completo', 250);
            $table->string('numero_documento', 20)->unique()->nullable();
            $table->string('tipo_documento', 10)->default('CC');
            $table->string('tarjeta_profesional', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1=Activo, 0=Inactivo');
            $table->timestamps();

            $table->foreign('id_cargo')->references('id_cargo')->on('cargos');
            $table->foreign('id_especialidad')->references('id_especialidad')->on('especialidades');
        });

        // ================================================================
        // PACIENTES (depende de aseguradoras, usuarios, barrios)
        // ================================================================

        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('id_paciente');
            $table->string('tipo_documento', 5)->default('CC');
            $table->string('identificacion', 20)->unique();
            $table->string('nombre_completo', 200);
            $table->date('fecha_nacimiento');
            $table->string('sexo', 1);
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->unsignedInteger('id_aseguradora');
            $table->string('regimen', 50)->default('CONTRIBUTIVO');
            $table->unsignedInteger('id_madrina')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('direccion', 255);
            $table->unsignedInteger('id_barrio')->nullable();
            $table->unsignedInteger('id_comuna')->nullable();
            $table->integer('orden_mapa')->default(0)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('url_google_maps')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();

            $table->foreign('id_aseguradora')->references('id_aseguradora')->on('aseguradoras');
            $table->foreign('id_madrina')->references('id_usuario')->on('usuarios')->onDelete('set null');
            $table->foreign('id_barrio')->references('id_barrio')->on('barrios');
        });

        // ================================================================
        // CUIDADORES (depende de pacientes)
        // ================================================================

        Schema::create('cuidadores', function (Blueprint $table) {
            $table->increments('id_cuidador');
            $table->unsignedInteger('id_paciente');
            $table->string('nombre_completo', 200);
            $table->string('parentesco', 50)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->tinyInteger('es_principal')->default(1);
            $table->string('tipo_auxiliar', 10)->nullable();
            $table->tinyInteger('horas_diarias')->nullable()->unsigned();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes')->onDelete('cascade');
        });

        // ================================================================
        // TUTELAS (depende de pacientes)
        // ================================================================

        Schema::create('tutelas', function (Blueprint $table) {
            $table->increments('id_tutela');
            $table->unsignedInteger('id_paciente');
            $table->string('numero_tutela', 100);
            $table->date('fecha_tutela')->nullable();
            $table->tinyInteger('prestacion_autorizada')->default(0);
            $table->tinyInteger('es_permanente')->default(0)->comment('1=Sin fecha fin');
            $table->integer('duracion_dias')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes')->onDelete('cascade');
        });

        // ================================================================
        // DIAGNÓSTICOS DEL PACIENTE (tabla pivote)
        // ================================================================

        Schema::create('paciente_diagnosticos', function (Blueprint $table) {
            $table->unsignedInteger('id_paciente');
            $table->string('codigo_cie10', 10);
            $table->string('tipo_diagnostico', 30)->default('DOMICILIARIO');
            $table->tinyInteger('es_principal')->default(0);
            $table->date('fecha_registro')->nullable();
            $table->string('observacion', 500)->nullable();

            $table->primary(['id_paciente', 'codigo_cie10', 'tipo_diagnostico']);

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes')->onDelete('cascade');
            $table->foreign('codigo_cie10')->references('codigo')->on('diagnosticos_cie10');
        });

        // ================================================================
        // SOLICITUDES DE EQUIPOS
        // ================================================================

        Schema::create('solicitudes_equipos', function (Blueprint $table) {
            $table->increments('id_solicitud');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_usuario_gestiona')->nullable();
            $table->string('modalidad', 20)->nullable();
            $table->string('tiempo_requerido', 20)->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->date('fecha_solicitud')->nullable()->default(DB::raw('(curdate())'));
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_devolucion_esperada')->nullable();
            $table->date('fecha_devolucion_real')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_usuario_gestiona')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });

        Schema::create('detalle_solicitud_equipos', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_solicitud');
            $table->unsignedInteger('id_equipo');
            $table->tinyInteger('cantidad')->default(1)->unsigned();
            $table->string('observacion', 300)->nullable();

            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_equipos')->onDelete('cascade');
            $table->foreign('id_equipo')->references('id_equipo')->on('catalogo_equipos');
        });

        // ================================================================
        // ÓRDENES MÉDICAS
        // ================================================================

        Schema::create('ordenes_medicas', function (Blueprint $table) {
            $table->increments('id_orden');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_especialidad');
            $table->unsignedInteger('id_personal_ordena')->nullable();
            $table->date('fecha_orden');
            $table->smallInteger('numero_sesiones')->default(1)->unsigned();
            $table->smallInteger('frecuencia_dias')->default(0)->unsigned();
            $table->string('numero_mipres', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20)->default('VIGENTE');
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_especialidad')->references('id_especialidad')->on('especialidades');
            $table->foreign('id_personal_ordena')->references('id_personal')->on('personal')->onDelete('set null');
        });

        // ================================================================
        // VISITAS DOMICILIARIAS
        // ================================================================

        Schema::create('visitas_domiciliarias', function (Blueprint $table) {
            $table->increments('id_visita');
            $table->unsignedInteger('id_orden_asociada')->nullable();
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_personal');
            $table->unsignedInteger('id_especialidad');
            $table->unsignedInteger('id_usuario_programa')->nullable();
            $table->dateTime('fecha_programada');
            $table->dateTime('fecha_realizada')->nullable();
            $table->decimal('latitud_checkin', 10, 8)->nullable();
            $table->decimal('longitud_checkin', 11, 8)->nullable();
            $table->decimal('latitud_checkout', 10, 8)->nullable();
            $table->decimal('longitud_checkout', 11, 8)->nullable();
            $table->string('estado', 20)->default('PROGRAMADA');
            $table->string('motivo_cancelacion', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_orden_asociada')->references('id_orden')->on('ordenes_medicas')->onDelete('set null');
            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_personal')->references('id_personal')->on('personal');
            $table->foreign('id_especialidad')->references('id_especialidad')->on('especialidades');
            $table->foreign('id_usuario_programa')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });

        // ================================================================
        // LABORATORIOS
        // ================================================================

        Schema::create('laboratorios', function (Blueprint $table) {
            $table->increments('id_laboratorio');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_orden_asociada')->nullable();
            $table->unsignedInteger('id_personal_toma')->nullable();
            $table->unsignedInteger('id_usuario_solicita')->nullable();
            $table->date('fecha_solicitud');
            $table->dateTime('fecha_toma_programada')->nullable();
            $table->dateTime('fecha_toma_real')->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->tinyInteger('confirmacion_toma')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_orden_asociada')->references('id_orden')->on('ordenes_medicas')->onDelete('set null');
            $table->foreign('id_personal_toma')->references('id_personal')->on('personal')->onDelete('set null');
            $table->foreign('id_usuario_solicita')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });

        // ================================================================
        // TELEXPERTICIAS
        // ================================================================

        Schema::create('telexperticias', function (Blueprint $table) {
            $table->increments('id_telexperticia');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_especialidad');
            $table->unsignedInteger('id_usuario_solicita')->nullable();
            $table->date('fecha_solicitud');
            $table->smallInteger('frecuencia_dias')->nullable();
            $table->string('estado', 20)->default('SOLICITADA');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_especialidad')->references('id_especialidad')->on('especialidades');
            $table->foreign('id_usuario_solicita')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * Se eliminan en orden inverso para respetar las FK.
     */
    public function down(): void
    {
        Schema::dropIfExists('telexperticias');
        Schema::dropIfExists('laboratorios');
        Schema::dropIfExists('visitas_domiciliarias');
        Schema::dropIfExists('ordenes_medicas');
        Schema::dropIfExists('detalle_solicitud_equipos');
        Schema::dropIfExists('solicitudes_equipos');
        Schema::dropIfExists('paciente_diagnosticos');
        Schema::dropIfExists('tutelas');
        Schema::dropIfExists('cuidadores');
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('personal');
        Schema::dropIfExists('barrios');
        Schema::dropIfExists('comunas');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('catalogo_equipos');
        Schema::dropIfExists('diagnosticos_cie10');
        Schema::dropIfExists('especialidades');
        Schema::dropIfExists('aseguradoras');
        Schema::dropIfExists('zonas');
    }
};
