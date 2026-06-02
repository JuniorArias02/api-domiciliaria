<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('rutas')) {
            Schema::create('rutas', function (Blueprint $table) {
                $table->increments('id_ruta');
                $table->unsignedInteger('id_personal');
                $table->date('fecha_ruta');
                $table->enum('estado', ['EN_DISENO', 'ASIGNADA', 'EN_RECORRIDO', 'FINALIZADA'])->default('EN_DISENO');
                $table->timestamps();

                $table->foreign('id_personal')->references('id_personal')->on('personal');
            });
        }

        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            if (!Schema::hasColumn('visitas_domiciliarias', 'id_ruta')) {
                $table->unsignedInteger('id_ruta')->nullable()->after('id_personal');
                $table->foreign('id_ruta')->references('id_ruta')->on('rutas')->onDelete('set null');
            }
            if (!Schema::hasColumn('visitas_domiciliarias', 'orden_visita')) {
                $table->integer('orden_visita')->nullable()->after('id_ruta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            if (Schema::hasColumn('visitas_domiciliarias', 'id_ruta')) {
                $table->dropForeign(['id_ruta']);
                $table->dropColumn('id_ruta');
            }
            if (Schema::hasColumn('visitas_domiciliarias', 'orden_visita')) {
                $table->dropColumn('orden_visita');
            }
        });

        Schema::dropIfExists('rutas');
    }
};
