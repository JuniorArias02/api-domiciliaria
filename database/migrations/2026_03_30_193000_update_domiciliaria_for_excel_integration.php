<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración aplica los cambios estructurales DEFINITIVOS para integrar los datos
     * del archivo de Excel TOTALMENTE REVISADO (Marzo 2026).
     * 
     * Se han descartado campos de aseguradora a petición del usuario.
     */
    public function up(): void
    {
        // 1. Catálogo de Servicios (Se crea antes para las FK)
        if (!Schema::hasTable('servicios')) {
            Schema::create('servicios', function (Blueprint $table) {
                $table->increments('id_servicio');
                $table->string('codigo_servicio', 50)->unique();
                $table->string('nombre_servicio', 255);
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        // 2. Extensión de Visitas Domiciliarias
        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            // El codigo_ingreso es fundamental para evitar duplicar diagnósticos por visita.
            if (!Schema::hasColumn('visitas_domiciliarias', 'codigo_ingreso')) {
                $table->string('codigo_ingreso', 20)->nullable()->unique()->after('id_visita');
            }
            if (!Schema::hasColumn('visitas_domiciliarias', 'tipo_atencion_ext')) {
                $table->string('tipo_atencion_ext', 100)->nullable();
            }
            if (!Schema::hasColumn('visitas_domiciliarias', 'remitido_a')) {
                $table->string('remitido_a', 255)->nullable();
            }
            if (!Schema::hasColumn('visitas_domiciliarias', 'id_servicio')) {
                $table->unsignedInteger('id_servicio')->nullable();
                $table->foreign('id_servicio')
                      ->references('id_servicio')
                      ->on('servicios')
                      ->onDelete('set null');
            }
        });

        // 3. Mejorar paciente_diagnosticos (Relación con Visita para trazabilidad)
        Schema::table('paciente_diagnosticos', function (Blueprint $table) {
            if (!Schema::hasColumn('paciente_diagnosticos', 'id_visita')) {
                // Se añade id_visita con valor 0 para registros antiguos.
                $table->unsignedInteger('id_visita')->default(0)->after('tipo_diagnostico');
                
                // Redefinición de PK para permitir diagnósticos repetidos en diferentes visitas.
                $table->dropPrimary(); 
                $table->primary(['id_paciente', 'codigo_cie10', 'tipo_diagnostico', 'id_visita']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paciente_diagnosticos', function (Blueprint $table) {
            if (Schema::hasColumn('paciente_diagnosticos', 'id_visita')) {
                $table->dropPrimary();
                $table->primary(['id_paciente', 'codigo_cie10', 'tipo_diagnostico']);
                $table->dropColumn('id_visita');
            }
        });

        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            if (Schema::hasColumn('visitas_domiciliarias', 'id_servicio')) {
                $table->dropForeign(['id_servicio']);
                $table->dropColumn(['codigo_ingreso', 'tipo_atencion_ext', 'remitido_a', 'id_servicio']);
            } else {
                $table->dropColumn(['codigo_ingreso', 'tipo_atencion_ext', 'remitido_a']);
            }
        });

        Schema::dropIfExists('servicios');
    }
};
